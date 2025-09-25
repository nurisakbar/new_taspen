<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;

class AuthResetPasswordController extends Controller
{
    public function requestReset(Request $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Reset password link sent successfully',
            'data' => [
                'email' => $request->input('email'),
                'request_id' => 'REQ-123456'
            ]
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


