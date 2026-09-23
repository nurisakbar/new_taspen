<?php

namespace App\Jobs;

use App\Models\PolisSend;
use App\Services\QontakService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendMessage implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $polisSendId)
    {
    }

    public function handle(QontakService $qontakService): void
    {
        $record = PolisSend::query()->find($this->polisSendId);
        if ($record === null) {
            Log::error('Polis send record not found', ['id' => $this->polisSendId]);
            return;
        }

        $content = [
            'customer_name' => $record->customer_name,
            'address' => $record->address,
            'polis_number' => $record->polis_number,
            'name_of_the_insured' => $record->name_of_the_insured,
            'product_name' => $record->product_name,
        ];

        $pdf = Pdf::loadView('polis_send', [
            'pesan' => $record,
            'content' => $content,
            'tanggalSurat' => $this->tanggalIndo($record->created_at),
        ]);

        $birthDate = $record->birth_date
            ? Carbon::parse($record->birth_date)
            : null;
        if ($birthDate) {
            $pdf->setEncryption('viewer_password', $birthDate->format('dmY'));
        }

        $fileName = $record->polis_number . '.pdf';
        $pdf->save(storage_path('app/public/polis-' . $fileName));

        $url = 'https://taspen.klikakutansi.com/storage/polis-' . $fileName;
        $filename = 'polis-verify/' . ($record->pesan_id ?? $record->id);

        $waPayload = [
            'to_name' => $record->customer_name,
            'to_number' => $qontakService->normalizeIndonesianMsisdn($record->phone_number),
            'message_template_id' => 'b54e5b8e-a81e-452f-b2db-04c79b67ef3a',
            'channel_integration_id' => '3702ae75-4d97-482c-969a-49f19254c418',
            'language' => ['code' => 'en'],
            'parameters' => [
                'header' => [
                    'format' => 'DOCUMENT',
                    'params' => [
                        ['key' => 'url', 'value' => $url],
                        ['key' => 'filename', 'value' => $fileName],
                    ],
                ],
                'body' => [
                    [
                        'key' => '1',
                        'value_text' => $record->customer_name,
                        'value' => 'tanggal1',
                    ],
                ],
                'buttons' => [
                    [
                        'index' => '0',
                        'type' => 'url',
                        'value' => $filename,
                    ],
                ],
            ],
        ];

        $whatsappResult = $qontakService->sendDirect($waPayload);

        $responseId = null;
        if (!empty($whatsappResult['success']) && !empty($whatsappResult['data'])) {
            $data = $whatsappResult['data'];
            $responseId = $data['id']
                ?? ($data['data']['id'] ?? null)
                ?? ($data['message_id'] ?? null)
                ?? null;
        }

        $record->update([
            'qontak_response' => $whatsappResult,
            'response_id' => $responseId,
        ]);
    }

    private function tanggalIndo($date): string
    {
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        $bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $carbon->format('d') . ' ' . $bulan[(int) $carbon->format('n')] . ' ' . $carbon->format('Y');
    }
}
