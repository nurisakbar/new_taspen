<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;
use App\Http\Requests\KlaimPembayaranRequest;
use App\Models\KlaimPembayaran;

class KlaimPembayaranController extends Controller
{
    public function bayar(KlaimPembayaranRequest $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ]);
        $validated = $request->validated();

        $record = KlaimPembayaran::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran klaim berhasil diproses',
            'data' => $record,
        ], 201);
    }
}


