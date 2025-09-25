<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;

class WelcomeGreetingController extends Controller
{
    public function index(Request $request)
    {
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Selamat datang di layanan TASPEN',
        ]);
    }
}


