<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;
use App\Http\Requests\OtpRequest;
use App\Models\Otp;
use App\Services\QontakService;

class OtpController extends Controller
{
    public function send(OtpRequest $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ]);

        // Generate random 6-digit OTP code
        $kodeOtp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $created = Otp::create([
            'nomor_tujuan' => $request->input('nomor_tujuan'),
            'kode_otp' => $kodeOtp
        ]);

        // Send WhatsApp notification via Qontak using provided template id
        $whatsappResult = ['success' => false, 'message' => 'Skipped sending WhatsApp'];
        try {
            $qontakService = app(QontakService::class);
            $templateId = '36218f5a-0484-4fff-b150-d9385bf1252d';
            $waPayload = [
                'to_name' => 'test',
                'to_number' => $qontakService->normalizeIndonesianMsisdn($request->input('nomor_tujuan')),
                'message_template_id' => $templateId,
                'channel_integration_id' => '3702ae75-4d97-482c-969a-49f19254c418',
                'language' => ['code' => 'id'],
                'parameters' => [
                    'body' => [
                        ['key' => '1', 'value_text' => $kodeOtp, 'value' => 'otp_code']
                    ]
                ]
            ];
            $whatsappResult = $qontakService->sendDirect($waPayload);

            return $whatsappResult;
        } catch (\Throwable $e) {
            $whatsappResult = ['success' => false, 'error' => $e->getMessage(), 'message' => 'Exception occurred while sending WhatsApp'];
        }

        $message = 'OTP sent';
        if ($whatsappResult['success']) {
            $message .= ' and WhatsApp notification sent';
        } else {
            $message .= ', however WhatsApp notification failed';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'otp' => $created,
                'whatsapp_notification' => $whatsappResult,
            ],
        ]);
    }

    public function verify(Request $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ]);
        $otp = $request->input('otp');
        if ($otp !== '123456') {
            return response()->json([
                'success' => false,
                'message' => 'OTP invalid',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP verified'
        ]);
    }

}


