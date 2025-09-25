<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;
class PendaftaranProdukController extends Controller
{
    public function daftar(Request $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ]);
        $product = $request->input('product');
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk wajib diisi'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran produk berhasil',
            'data' => [
                'registration_id' => 'REG-112233',
                'product' => $product
            ]
        ]);
    }
}


