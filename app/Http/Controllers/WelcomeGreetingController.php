<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;
use App\Http\Requests\WelcomeGreetingRequest;
use App\Models\WelcomeGreeting;

class WelcomeGreetingController extends Controller
{
    public function index(WelcomeGreetingRequest $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ]);

        $validated = $request->validated();

        $welcome = WelcomeGreeting::create([
            'nama_peserta' => $validated['nama_peserta'],
            'nomor_wa_tujuan' => $validated['nomor_wa_tujuan'],
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $welcome->id,
                'nama_peserta' => $welcome->nama_peserta,
                'nomor_wa_tujuan' => $welcome->nomor_wa_tujuan,
            ],
        ], 201);
    }
}


