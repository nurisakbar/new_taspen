<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;

class TshJatuhTempoController extends Controller
{
    public function index(Request $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Data jatuh tempo TSH',
            'data' => [
                [
                    'policy_number' => 'TSH-001',
                    'due_date' => '2025-10-01',
                    'amount_due' => 150000,
                ],
                [
                    'policy_number' => 'TSH-002',
                    'due_date' => '2025-11-15',
                    'amount_due' => 200000,
                ],
            ]
        ]);
    }
}


