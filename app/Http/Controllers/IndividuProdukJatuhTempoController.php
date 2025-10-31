<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;
use App\Models\IndividuProdukJatuhTempo;
use App\Http\Requests\IndividuProdukJatuhTempoRequest;
use App\Services\QontakService;

class IndividuProdukJatuhTempoController extends Controller
{
    public function index(IndividuProdukJatuhTempoRequest $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        $validated = $request->validated();

        $payload = [
            'nama_peserta' => $validated['nama_peserta'],
            'nomor_polis' => $validated['nomor_polis'],
            'nomor_va' => $validated['nomor_va'],
            'produk_asuransi' => $validated['produk_asuransi'],
            'premi_per_bulan' => $validated['premi_per_bulan'],
            'periode_tagihan' => $validated['periode_tagihan'],
            'jenis_jatuh_tempo' => 'AllProductJatuhTempo',
            'nomor_wa_tujuan' => $validated['nomor_wa_tujuan'],
        ];

        $record = IndividuProdukJatuhTempo::create($payload);

        // Build Qontak payload same as TBL (template id can be overridden via request)
        $messageTemplateId = $request->input('message_template_id', '4af41271-9485-4586-bb50-a98f78334b67');
        $qontakService = app(QontakService::class);
        $waPayload = [
            'to_name' => $payload['nama_peserta'],
            'to_number' => $qontakService->normalizeIndonesianMsisdn($validated['nomor_wa_tujuan']),
            'message_template_id' => $messageTemplateId,
            'channel_integration_id' => '3702ae75-4d97-482c-969a-49f19254c418',
            'language' => ['code' => 'id'],
            'parameters' => [
                'body' => [
                    [ 'key' => '1', 'value_text' => $payload['nama_peserta'], 'value' => 'customer_name' ],
                    [ 'key' => '3', 'value_text' => $payload['nomor_va'], 'value' => 'product_name' ],
                    [ 'key' => '2', 'value_text' => $payload['nomor_polis'], 'value' => 'policy_number' ],
                    [ 'key' => '4', 'value_text' => $payload['produk_asuransi'], 'value' => 'virtual_account' ],
                    [ 'key' => '5', 'value_text' => number_format($payload['premi_per_bulan'], 0, ',', '.'), 'value' => 'premium_amount' ],
                    [ 'key' => '6', 'value_text' => $payload['periode_tagihan'], 'value' => 'billing_period' ],
                ]
            ],
        ];

        // Send WhatsApp notification via Qontak
        $whatsappResult = $qontakService->sendDirect($waPayload);

        // Save response body and id into individu_produk_jatuh_tempos
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

        $message = 'Data jatuh tempo AllProduct berhasil dibuat';
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
