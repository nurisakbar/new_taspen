<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;
use App\Http\Requests\TssLapseRequest;
use App\Http\Requests\TblLapseRequest;
use App\Http\Requests\ThcpTshLapseRequest;
use App\Models\TssLapse;
use App\Models\TblLapse;
use App\Models\ThcpTshLapse;
use App\Services\QontakService;

class LapseController extends Controller
{
    public function tss(TssLapseRequest $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        $validated = $request->validated();

        $payload = [
            'nama_peserta' => $validated['nama_peserta'],
            'nomor_polis' => $validated['nomor_polis'],
            'produk_asuransi' => $validated['nama_produk'],
            'nomor_wa_tujuan' => $validated['nomor_wa_tujuan'] ?? '',
        ];

        $record = TssLapse::create($payload);

        // Build Qontak payload for TSS Lapse
        $messageTemplateId = $request->input('message_template_id', 'b096079a-15da-4900-85c0-3da89e466c40');
        $qontakService = app(QontakService::class);
        $waPayload = [
            'to_name' => $payload['nama_peserta'],
            'to_number' => $qontakService->normalizeIndonesianMsisdn($validated['nomor_wa_tujuan'] ?? ''),
            'message_template_id' => $messageTemplateId,
            'channel_integration_id' => '3702ae75-4d97-482c-969a-49f19254c418',
            'language' => ['code' => 'id'],
            'parameters' => [
                'body' => [
                    [ 'key' => '1', 'value_text' => $payload['nama_peserta'], 'value' => 'customer_name' ],
                    [ 'key' => '2', 'value_text' => $payload['produk_asuransi'], 'value' => 'product_name' ],
                    [ 'key' => '3', 'value_text' => $payload['nomor_polis'], 'value' => 'policy_number' ],
                ]
            ],
        ];

        // Send TSS Lapse WhatsApp notification via Qontak
        $whatsappResult = $qontakService->sendDirect($waPayload);

        // Save response body and id into tss_lapses
        try {
            $record->qontak_response_body = $whatsappResult['data'] ?? ($whatsappResult['error_parsed'] ?? ($whatsappResult['error'] ?? $whatsappResult));
            if (!empty($whatsappResult['success']) && !empty($whatsappResult['data'])) {
                $data = $whatsappResult['data'];
                $responseId = $data['id']
                    ?? ($data['data']['id'] ?? null)
                    ?? ($data['message_id'] ?? null)
                    ?? ($data['result']['id'] ?? null)
                    ?? null;
                if (!empty($responseId)) {
                    $record->qontak_response_id = $responseId;
                }
            }
            $record->save();
        } catch (\Throwable $e) {
            // keep silent, do not break main flow
        }

        $message = 'Data lapse TSS berhasil dibuat';
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
                'record' => $record,
                'whatsapp_notification' => $whatsappResult,
            ],
        ], $success ? 201 : 200);
    }

    public function tbl(TblLapseRequest $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        $validated = $request->validated();

        $payload = [
            'nama_peserta' => $validated['nama_peserta'],
            'nomor_polis' => $validated['nomor_polis'],
            'produk_asuransi' => $validated['nama_produk'],
            'nomor_wa_tujuan' => $validated['nomor_wa_tujuan'] ?? '',
        ];

        $record = TblLapse::create($payload);

        // Build Qontak payload for TBL Lapse
        $messageTemplateId = $request->input('message_template_id', '4950ec55-be83-4c76-8ba1-a9febee4db5b');
        $qontakService = app(QontakService::class);
        $waPayload = [
            'to_name' => $payload['nama_peserta'],
            'to_number' => $qontakService->normalizeIndonesianMsisdn($validated['nomor_wa_tujuan'] ?? ''),
            'message_template_id' => $messageTemplateId,
            'channel_integration_id' => '3702ae75-4d97-482c-969a-49f19254c418',
            'language' => ['code' => 'id'],
            'parameters' => [
                'body' => [
                    [ 'key' => '1', 'value_text' => $payload['nama_peserta'], 'value' => 'customer_name' ],
                    [ 'key' => '2', 'value_text' => $payload['produk_asuransi'], 'value' => 'product_name' ],
                    [ 'key' => '3', 'value_text' => $payload['nomor_polis'], 'value' => 'policy_number' ],
                ]
            ],
        ];

        // Send TBL Lapse WhatsApp notification via Qontak
        $whatsappResult = $qontakService->sendDirect($waPayload);

        // Save response body and id into tbl_lapses
        try {
            $record->qontak_response_body = $whatsappResult['data'] ?? ($whatsappResult['error_parsed'] ?? ($whatsappResult['error'] ?? $whatsappResult));
            if (!empty($whatsappResult['success']) && !empty($whatsappResult['data'])) {
                $data = $whatsappResult['data'];
                $responseId = $data['id']
                    ?? ($data['data']['id'] ?? null)
                    ?? ($data['message_id'] ?? null)
                    ?? ($data['result']['id'] ?? null)
                    ?? null;
                if (!empty($responseId)) {
                    $record->qontak_response_id = $responseId;
                }
            }
            $record->save();
        } catch (\Throwable $e) {
            // keep silent, do not break main flow
        }

        $message = 'Data lapse TBL berhasil dibuat';
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
                'record' => $record,
                'whatsapp_notification' => $whatsappResult,
            ],
        ], $success ? 201 : 200);
    }

    public function thcptsh(ThcpTshLapseRequest $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        $validated = $request->validated();

        $payload = [
            'nama_peserta' => $validated['nama_peserta'],
            'nomor_polis' => $validated['nomor_polis'],
            'produk_asuransi' => $validated['nama_produk'],
            'nomor_wa_tujuan' => $validated['nomor_wa_tujuan'] ?? '',
        ];

        $record = ThcpTshLapse::create($payload);

        // Build Qontak payload for THCPTSH Lapse
        $messageTemplateId = $request->input('message_template_id', '9f954286-0c70-4f14-bcb2-f68401a2f294');
        $qontakService = app(QontakService::class);
        $waPayload = [
            'to_name' => $payload['nama_peserta'],
            'to_number' => $qontakService->normalizeIndonesianMsisdn($validated['nomor_wa_tujuan'] ?? ''),
            'message_template_id' => $messageTemplateId,
            'channel_integration_id' => '3702ae75-4d97-482c-969a-49f19254c418',
            'language' => ['code' => 'id'],
            'parameters' => [
                'body' => [
                    [ 'key' => '1', 'value_text' => $payload['nama_peserta'], 'value' => 'customer_name' ],
                    [ 'key' => '2', 'value_text' => $payload['produk_asuransi'], 'value' => 'product_name' ],
                    [ 'key' => '3', 'value_text' => $payload['nomor_polis'], 'value' => 'policy_number' ],
                ]
            ],
        ];

        // Send THCPTSH Lapse WhatsApp notification via Qontak
        $whatsappResult = $qontakService->sendDirect($waPayload);

        // Save response body and id into thcp_tsh_lapses
        try {
            $record->qontak_response_body = $whatsappResult['data'] ?? ($whatsappResult['error_parsed'] ?? ($whatsappResult['error'] ?? $whatsappResult));
            if (!empty($whatsappResult['success']) && !empty($whatsappResult['data'])) {
                $data = $whatsappResult['data'];
                $responseId = $data['id']
                    ?? ($data['data']['id'] ?? null)
                    ?? ($data['message_id'] ?? null)
                    ?? ($data['result']['id'] ?? null)
                    ?? null;
                if (!empty($responseId)) {
                    $record->qontak_response_id = $responseId;
                }
            }
            $record->save();
        } catch (\Throwable $e) {
            // keep silent, do not break main flow
        }

        $message = 'Data lapse THCPTSH berhasil dibuat';
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
                'record' => $record,
                'whatsapp_notification' => $whatsappResult,
            ],
        ], $success ? 201 : 200);
    }
}
