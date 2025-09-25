<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;
use App\Http\Requests\IndividuProdukJatuhTempoRequest;
use App\Models\IndividuProdukJatuhTempo;

class TshJatuhTempoController extends Controller
{
    public function index(IndividuProdukJatuhTempoRequest $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ]);
        $validated = $request->validated();

        $payload = [
            'nama_peserta' => $validated['nama_peserta'],
            'nomor_polis' => $validated['nomor_polis'],
            'nomor_va' => $validated['nomor_va'],
            'produk_asuransi' => $validated['produk_asuransi'],
            'premi_per_bulan' => $validated['premi_per_bulan'],
            'periode_tagihan' => $validated['periode_tagihan'],
            'jenis_jatuh_tempo' => 'tsh',
            'nomor_wa_tujuan' => $validated['nomor_wa_tujuan'],
        ];

        $record = IndividuProdukJatuhTempo::create($payload);

        return response()->json([
            'success' => true,
            'message' => 'Data jatuh tempo TSH berhasil dibuat',
            'data' => $record,
        ], 201);
    }
}


