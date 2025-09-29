<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthResetPasswordController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\KlaimPembayaranController;
use App\Http\Controllers\TshJatuhTempoController;
use App\Http\Controllers\TblJatuhTempoController;
use App\Http\Controllers\IndividuProdukJatuhTempoController;
use App\Http\Controllers\TshKartuPesertaController;
use App\Http\Controllers\WelcomeGreetingController;
use App\Http\Controllers\PendaftaranProdukController;

Route::middleware(['api','verify.api.token'])->group(function () {
    // Reset Password
    Route::post('/auth/reset/request', [AuthResetPasswordController::class, 'requestReset']);
    Route::post('/auth/reset/confirm', [AuthResetPasswordController::class, 'confirmReset']);

    // OTP
    Route::post('/otp/send', [OtpController::class, 'send']);
    Route::post('/otp/verify', [OtpController::class, 'verify']);

    // Pembayaran Klaim
    Route::post('/klaim/bayar', [KlaimPembayaranController::class, 'bayar']);
    Route::post('/klaim/template/variables', [KlaimPembayaranController::class, 'templateVariables']);

    // Jatuh Tempo
    Route::post('/tsh/jatuh-tempo', [TshJatuhTempoController::class, 'index']);
    Route::post('/tbl/jatuh-tempo', [TblJatuhTempoController::class, 'index']);
    Route::post('/all-product/jatuh-tempo', [IndividuProdukJatuhTempoController::class, 'index']);

    // Informasi Kartu Peserta TSH
    Route::post('/tsh/kartu-peserta', [TshKartuPesertaController::class, 'show']);

    // Welcome Greeting
    Route::post('/welcome', [WelcomeGreetingController::class, 'index']);

    // Pendaftaran Produk
    Route::post('/produk/daftar', [PendaftaranProdukController::class, 'daftar']);
});


