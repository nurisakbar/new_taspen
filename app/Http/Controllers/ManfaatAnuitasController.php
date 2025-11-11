<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;
use App\Http\Requests\ManfaatAnuitasRequest;
use App\Models\ManfaatAnuitas;
use App\Services\QontakService;

class ManfaatAnuitasController extends Controller
{
    /**
     * Format number to Indonesian Rupiah format
     * 
     * @param mixed $amount
     * @return string
     */
    private function formatRupiah($amount)
    {
        return 'Rp ' . number_format((float) $amount, 0, ',', '.');
    }

    public function show(ManfaatAnuitasRequest $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
        $validated = $request->validated();

        $record = ManfaatAnuitas::create([
            'nama_peserta' => $validated['nama_peserta'],
            'nomor_peserta' => $validated['nomor_peserta'],
            'periode' => $validated['periode'],
            'nilai_manfaat_bulanan' => $validated['nilai_manfaat_bulanan'],
            'saldo_nilai_tunai' => $validated['saldo_nilai_tunai'],
        ]);

        // Build and send WhatsApp via Qontak (template id provided by user)
        $messageTemplateId = $request->input('message_template_id', '64ba6f91-5d1d-4f7f-8939-ef8572f731ce');
        $qontakService = app(QontakService::class);
        $waPayload = [
            'to_name' => $validated['nama_peserta'],
            'to_number' => $qontakService->normalizeIndonesianMsisdn($validated['nomor_wa_tujuan']),
            'message_template_id' => $messageTemplateId,
            'channel_integration_id' => '3702ae75-4d97-482c-969a-49f19254c418',
            'language' => ['code' => 'id'],
            'parameters' => [
                'body' => [
                    [ 'key' => '1', 'value_text' => $validated['nama_peserta'], 'value' => 'customer_name' ],
                    [ 'key' => '2', 'value_text' => $validated['nomor_peserta'], 'value' => 'part_number' ],
                    [ 'key' => '3', 'value_text' => $validated['periode'], 'value' => 'period' ],
                    [ 'key' => '4', 'value_text' => $this->formatRupiah($validated['nilai_manfaat_bulanan']), 'value' => 'benefit_amount' ],
                    [ 'key' => '5', 'value_text' => $this->formatRupiah($validated['saldo_nilai_tunai']), 'value' => 'cash_amount' ],
                    [ 'key' => '6', 'value_text' => 'TL Care', 'value' => 'service_name' ],
                    [ 'key' => '7', 'value_text' => '0811 8111 1808 (WhatsApp Chat)', 'value' => 'whatsapp_number' ],
                    [ 'key' => '8', 'value_text' => 'tlscenter@taspenlife.com', 'value' => 'email' ],
                    [ 'key' => '9', 'value_text' => 'PT Asuransi Jiwa Taspen', 'value' => 'company_name' ],
                ]
            ],
        ];
        

        $whatsappResult = $qontakService->sendDirect($waPayload);

        $message = 'Data manfaat anuitas berhasil dibuat';
        $success = true;
        if (!$whatsappResult['success']) {
            $message .= ', namun notifikasi WhatsApp gagal dikirim';
            $success = false;
        } else {
            $message .= ' dan notifikasi WhatsApp berhasil dikirim';
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => [
                'id' => $record->id,
                'nama_peserta' => $record->nama_peserta,
                'nomor_peserta' => $record->nomor_peserta,
                'periode' => $record->periode,
                'nilai_manfaat_bulanan' => $this->formatRupiah($record->nilai_manfaat_bulanan),
                'saldo_nilai_tunai' => $this->formatRupiah($record->saldo_nilai_tunai),
                'whatsapp_notification' => $whatsappResult,
            ],
        ], $success ? 201 : 200);
    }
}

