<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;

class IndividuProdukJatuhTempoController extends Controller
{
    public function index(Request $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Data jatuh tempo semua produk individu',
            'data' => [
                [
                    'product' => 'TSH',
                    'policy_number' => 'TSH-777',
                    'due_date' => '2025-12-01',
                    'amount_due' => 250000,
                ],
                [
                    'product' => 'TBL',
                    'policy_number' => 'TBL-888',
                    'due_date' => '2025-12-15',
                    'amount_due' => 125000,
                ],
            ]
        ]);
    }
}


