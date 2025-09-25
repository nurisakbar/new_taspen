<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;
use App\Http\Requests\PendaftaranProdukRequest;
use App\Models\PendaftaranProduk;
class PendaftaranProdukController extends Controller
{
    public function daftar(PendaftaranProdukRequest $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ]);
        $validated = $request->validated();
        $pendaftaran = PendaftaranProduk::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran produk berhasil',
            'data' => [
                'id' => $pendaftaran->id,
                'nama_peserta' => $pendaftaran->nama_peserta,
                'nama_produk' => $pendaftaran->nama_produk,
                'jumlah_premi' => $pendaftaran->jumlah_premi,
                'nomor_va' => $pendaftaran->nomor_va,
                'nomor_wa_tujuan' => $pendaftaran->nomor_wa_tujuan,
                'link_pengkinian_data' => $pendaftaran->link_pengkinian_data
            ]
        ], 201);
    }
}


