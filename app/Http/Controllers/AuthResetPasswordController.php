<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;
use App\Http\Requests\PasswordSementaraRequest;
use App\Models\ResetPassword;

class AuthResetPasswordController extends Controller
{
    public function requestReset(PasswordSementaraRequest $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ]);

        $created = ResetPassword::create([
            'nomor_tujuan' => $request->input('nomor_tujuan'),
            'password_sementara' => $request->input('password_sementara'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reset password request stored',
            'data' => $created,
        ], 200);
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


