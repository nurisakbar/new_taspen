<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;
use App\Http\Requests\PasswordSementaraRequest;
use App\Models\ResetPassword;
use App\Services\QontakService;

class AuthResetPasswordController extends Controller
{
    public function requestReset(PasswordSementaraRequest $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ]);
        $validated = $request->validated();

        $payload = [
            'nomor_tujuan' => $validated['nomor_wa_tujuan'],
            'password_sementara' => $validated['password_sementara'],
        ];

        $record = ResetPassword::create($payload);

        // Build and send WhatsApp via Qontak (template id provided by user)
        $messageTemplateId = $request->input('message_template_id', '85967a89-ecc6-48bb-ae76-4c68606fd2bb');
        $qontakService = app(QontakService::class);
        $waPayload = [
            'to_name' => 'test',
            'to_number' => $qontakService->normalizeIndonesianMsisdn($validated['nomor_wa_tujuan']),
            'message_template_id' => $messageTemplateId,
            'channel_integration_id' => '3702ae75-4d97-482c-969a-49f19254c418',
            'language' => ['code' => 'id'],
            'parameters' => [
                'body' => [
                    [ 'key' => '1', 'value_text' => 'reset password', 'value' => 'reset_password' ],
                    [ 'key' => '2', 'value_text' => $payload['password_sementara'], 'value' => 'sementara' ],
                    [ 'key' => '3', 'value_text' => 'ubah password', 'value' => 'ubah_password' ],
                ]
            ],
        ];

        $whatsappResult = $qontakService->sendDirect($waPayload);

        // Save response body and id into reset_passwords
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

        $message = 'Data reset password berhasil dibuat';
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

    public function confirmReset(Request $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ]);
        $token = $request->input('token');
        if ($token !== 'valid-token') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token',
                'errors' => [
                    'token' => 'Token is not valid'
                ]
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password reset successful'
        ], 200);
    }
}


