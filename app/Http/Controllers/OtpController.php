<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;
use App\Http\Requests\OtpRequest;
use App\Models\Otp;

class OtpController extends Controller
{
    public function send(OtpRequest $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ]);

        $created = Otp::create([
            'nomor_tujuan' => $request->input('nomor_tujuan')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP sent',
            'data' => $created,
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


