<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;

class OtpController extends Controller
{
    public function send(Request $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ]);
        return response()->json([
            'success' => true,
            'message' => 'OTP sent',
            'data' => [
                'destination' => $request->input('destination'),
                'otp' => '123456'
            ]
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


