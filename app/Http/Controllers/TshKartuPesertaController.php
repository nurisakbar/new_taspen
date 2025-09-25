<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;
use App\Http\Requests\TshKartuPesertaRequest;
use App\Models\TshKartuPeserta;

class TshKartuPesertaController extends Controller
{
    public function show(TshKartuPesertaRequest $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ]);
        $validated = $request->validated();

        $record = TshKartuPeserta::create([
            'nama_peserta' => $validated['nama_peserta'],
            'nomor_wa_tujuan' => $validated['nomor_wa_tujuan'],
            'nomor_kartu' => $validated['nomor_kartu'],
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $record->id,
                'nama_peserta' => $record->nama_peserta,
                'nomor_wa_tujuan' => $record->nomor_wa_tujuan,
                'nomor_kartu' => $record->nomor_kartu,
            ],
        ], 201);
    }
}


