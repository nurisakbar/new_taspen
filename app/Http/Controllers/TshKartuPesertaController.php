<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;
use App\Http\Requests\TshKartuPesertaRequest;
use App\Models\TshKartuPeserta;
use App\Services\QontakService;

class TshKartuPesertaController extends Controller
{
    public function show(TshKartuPesertaRequest $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ]);
        $validated = $request->validated();

        $record = TshKartuPeserta::create([
            'nama_peserta' => $validated['nama_peserta'],
            'nomor_wa_tujuan' => $validated['nomor_wa_tujuan'],
            'nomor_kartu' => $validated['nomor_kartu'],
        ]);

        // Build and send WhatsApp via Qontak (template id provided by user)
        $messageTemplateId = $request->input('message_template_id', 'c897c1ff-2035-40dc-a440-681840318ffb');
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
                    [ 'key' => '2', 'value_text' => $validated['nomor_kartu'], 'value' => 'card_number' ],
                ]
            ],
        ];

        $whatsappResult = $qontakService->sendDirect($waPayload);

        $message = 'Data kartu peserta TSH berhasil dibuat';
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
                'nomor_wa_tujuan' => $record->nomor_wa_tujuan,
                'nomor_kartu' => $record->nomor_kartu,
                'whatsapp_notification' => $whatsappResult,
            ],
        ], $success ? 201 : 200);
    }
}


