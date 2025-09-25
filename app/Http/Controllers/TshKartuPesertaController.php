<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;

class TshKartuPesertaController extends Controller
{
    public function show(Request $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ]);
        $nomor = $request->input('nomor_peserta', 'TSH-0001');
        return response()->json([
            'success' => true,
            'message' => 'Informasi kartu peserta TSH',
            'data' => [
                'nomor_peserta' => $nomor,
                'nama' => 'Budi Santoso',
                'status' => 'AKTIF',
                'berlaku_sampai' => '2026-01-01',
            ]
        ]);
    }
}


