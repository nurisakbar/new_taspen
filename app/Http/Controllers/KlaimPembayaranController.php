<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;
use App\Http\Requests\KlaimPembayaranRequest;
use App\Models\KlaimPembayaran;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use App\Models\QontakResponse;
use App\Services\QontakService;

class KlaimPembayaranController extends Controller
{
    public function bayar(KlaimPembayaranRequest $request, QontakService $qontakService)
    {
        // Log the incoming request
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ]);
        
        $validated = $request->validated();

        // Create the claim payment record
        $record = KlaimPembayaran::create($validated);

        // Send WhatsApp notification via Qontak Service
        $whatsappResult = $qontakService->sendWhatsAppDirect($validated);

        // Save response body into klaim_pembayarans
        try {
            $record->qontak_response_body = $whatsappResult['data'] ?? ($whatsappResult['error_parsed'] ?? ($whatsappResult['error'] ?? $whatsappResult));
            // Save response id only when success
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
            Log::warning('Failed to save qontak_response_body to klaim_pembayarans', [
                'id' => $record->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Prepare response data
        $responseData = [
            'klaim_pembayaran' => $record,
            'whatsapp_notification' => $whatsappResult
        ];

        // Determine response message based on WhatsApp result
        $message = 'Pembayaran klaim berhasil diproses';
        if (!$whatsappResult['success']) {
            $message .= ', namun notifikasi WhatsApp gagal dikirim';
        } else {
            $message .= ' dan notifikasi WhatsApp berhasil dikirim';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $responseData,
        ], 201);
    }

    private function sendQontakWhatsApp($claimData)
    {
        // Kept for backward compat if any other callers still reference it.
        $service = app(\App\Services\QontakService::class);
        return $service->sendWhatsAppDirect($claimData);
    }

    private function getQontakAccessToken(): array
    {
        $service = app(\App\Services\QontakService::class);
        return $service->getAccessToken();
    }

    private function refreshQontakToken(string $refreshToken): array
    {
        $service = app(\App\Services\QontakService::class);
        return $service->refreshToken($refreshToken);
    }

    private function normalizeIndonesianMsisdn($input): string
    {
        $number = trim((string) $input);
        if ($number === '') {
            return $number;
        }

        // Remove spaces, dashes, and parentheses
        $number = preg_replace('/[\s\-()]/', '', $number);
        if ($number === null) {
            $number = '';
        }

        // Remove leading '+' if present
        if (strpos($number, '+') === 0) {
            $number = substr($number, 1);
        }

        // Keep only digits
        $number = preg_replace('/\D/', '', $number) ?? '';

        if ($number === '') {
            return $number;
        }

        if (strpos($number, '62') === 0) {
            return $number;
        }

        if (strpos($number, '0') === 0) {
            return '62' . substr($number, 1);
        }

        // If starts with '8' (common local shorthand), prefix with '62'
        if (strpos($number, '8') === 0) {
            return '62' . $number;
        }

        return $number;
    }
}


