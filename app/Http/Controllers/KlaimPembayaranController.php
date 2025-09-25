<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;

class KlaimPembayaranController extends Controller
{
    public function bayar(Request $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ]);
        $amount = (float) $request->input('amount', 0);
        if ($amount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah pembayaran tidak valid'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran klaim berhasil diproses',
            'data' => [
                'transaction_id' => 'TRX-987654',
                'amount' => $amount,
                'status' => 'PAID'
            ]
        ], 200);
    }
}


