<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;

class TblJatuhTempoController extends Controller
{
    public function index(Request $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Data jatuh tempo TBL',
            'data' => [
                [
                    'policy_number' => 'TBL-101',
                    'due_date' => '2025-09-30',
                    'amount_due' => 100000,
                ],
                [
                    'policy_number' => 'TBL-202',
                    'due_date' => '2025-10-20',
                    'amount_due' => 175000,
                ],
            ]
        ]);
    }
}


