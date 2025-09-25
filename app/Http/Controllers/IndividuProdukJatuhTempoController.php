<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;
use App\Models\IndividuProdukJatuhTempo;
use App\Http\Requests\IndividuProdukJatuhTempoRequest;
use Carbon\Carbon;

class IndividuProdukJatuhTempoController extends Controller
{
    public function index(IndividuProdukJatuhTempoRequest $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ]);

        $payload = [
            'nama_peserta' => $request->input('nama_peserta'),
            'nomor_polis' => $request->input('nomor_polis'),
            'nomor_va' => $request->input('nomor_va'),
            'produk_asuransi' => $request->input('produk_asuransi'),
            'premi_per_bulan' => $request->input('premi_per_bulan'),
            'periode_tagihan' => $request->input('periode_tagihan'),
            'jenis_jatuh_tempo' => 'all_individu',
            'nomor_wa_tujuan' => $request->input('nomor_wa_tujuan'),
        ];



        $record = IndividuProdukJatuhTempo::create($payload);

        return response()->json([
            'success' => true,
            'message' => 'Data jatuh tempo produk individu berhasil dibuat',
            'data' => $record,
        ], 201);
    }
}


