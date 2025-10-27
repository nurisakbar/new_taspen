<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;
use App\Http\Requests\PendaftaranProdukRequest;
use App\Models\PendaftaranProduk;
use App\Services\QontakService;

class PendaftaranProdukController extends Controller
{
    public function daftar(PendaftaranProdukRequest $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
        $validated = $request->validated();
        $pendaftaran = PendaftaranProduk::create($validated);

        // Send WhatsApp notification via Qontak using provided template id
        $whatsappResult = [ 'success' => false, 'message' => 'Skipped sending WhatsApp' ];
        try {
            $qontakService = app(QontakService::class);
            $templateId = 'd38507ca-a56e-4254-a89a-697ccae81a91';
            $waPayload = [
                'to_name' => $pendaftaran->nama_peserta ?? ($validated['nama_peserta'] ?? null),
                'to_number' => $qontakService->normalizeIndonesianMsisdn($pendaftaran->nomor_wa_tujuan ?? ($validated['nomor_wa_tujuan'] ?? '')),
                'message_template_id' => $templateId,
                'channel_integration_id' => '3702ae75-4d97-482c-969a-49f19254c418',
                'language' => ['code' => 'id'],
                'parameters' => [
                    'body' => [
                        [ 'key' => '1', 'value_text' => $pendaftaran->nama_peserta ?? '', 'value' => 'customer_name' ],
                        [ 'key' => '2', 'value_text' => $pendaftaran->nama_produk ?? '', 'value' => 'product_name' ],
                        [ 'key' => '3', 'value_text' => (string) ($pendaftaran->jumlah_premi ?? ''), 'value' => 'premium_amount' ],
                        [ 'key' => '4', 'value_text' => $pendaftaran->nomor_va ?? '', 'value' => 'virtual_account' ],
                        [ 'key' => '5', 'value_text' => $pendaftaran->link_pengkinian_data ?? '', 'value' => 'update_link' ],
                    ]
                ],
            ];
            $whatsappResult = $qontakService->sendDirect($waPayload);
        } catch (\Throwable $e) {
            $whatsappResult = [ 'success' => false, 'error' => $e->getMessage(), 'message' => 'Exception occurred while sending WhatsApp' ];
        }

        $message = 'Pendaftaran produk berhasil';
        $success = true;
        if (empty($whatsappResult['success'])) {
            $message .= ', namun notifikasi WhatsApp gagal dikirim';
            $success = false;
        } else {
            $message .= ' dan notifikasi WhatsApp berhasil dikirim';
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => [
                'id' => $pendaftaran->id,
                'nama_peserta' => $pendaftaran->nama_peserta,
                'nama_produk' => $pendaftaran->nama_produk,
                'jumlah_premi' => $pendaftaran->jumlah_premi,
                'nomor_va' => $pendaftaran->nomor_va,
                'nomor_wa_tujuan' => $pendaftaran->nomor_wa_tujuan,
                'link_pengkinian_data' => $pendaftaran->link_pengkinian_data,
                'whatsapp_notification' => $whatsappResult,
            ]
        ], $success ? 201 : 200);
    }
}
