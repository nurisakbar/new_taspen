<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Pesan;

class ApiLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_request_logs_pesan(): void
    {
        $payload = ['email' => 'user@example.com'];
        $this->postJson('/api/auth/reset/request', $payload)
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseCount('pesan', 1);
        $this->assertDatabaseHas('pesan', [
            'url_endpoint' => '/api/auth/reset/request',
        ]);
    }

    public function test_reset_password_confirm_logs_pesan(): void
    {
        $payload = ['token' => 'valid-token', 'password' => 'secret123'];
        $this->postJson('/api/auth/reset/confirm', $payload)
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('pesan', [
            'url_endpoint' => '/api/auth/reset/confirm',
        ]);
    }

    public function test_otp_send_and_verify_log_pesan(): void
    {
        $this->postJson('/api/otp/send', ['destination' => '08123456789'])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->postJson('/api/otp/verify', ['otp' => '123456'])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('pesan', ['url_endpoint' => '/api/otp/send']);
        $this->assertDatabaseHas('pesan', ['url_endpoint' => '/api/otp/verify']);
    }

    public function test_klaim_bayar_logs_pesan(): void
    {
        $this->postJson('/api/klaim/bayar', ['amount' => 1000])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('pesan', ['url_endpoint' => '/api/klaim/bayar']);
    }

    public function test_jatuh_tempo_endpoints_log_pesan(): void
    {
        $this->getJson('/api/tsh/jatuh-tempo')->assertStatus(200)->assertJson(['success' => true]);
        $this->getJson('/api/tbl/jatuh-tempo')->assertStatus(200)->assertJson(['success' => true]);
        $this->getJson('/api/individu/jatuh-tempo')->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseHas('pesan', ['url_endpoint' => '/api/tsh/jatuh-tempo']);
        $this->assertDatabaseHas('pesan', ['url_endpoint' => '/api/tbl/jatuh-tempo']);
        $this->assertDatabaseHas('pesan', ['url_endpoint' => '/api/individu/jatuh-tempo']);
    }

    public function test_tsh_kartu_peserta_logs_pesan(): void
    {
        $this->getJson('/api/tsh/kartu-peserta?nomor_peserta=TSH-0001')
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('pesan', ['url_endpoint' => '/api/tsh/kartu-peserta']);
    }

    public function test_welcome_and_pendaftaran_produk_log_pesan(): void
    {
        $this->getJson('/api/welcome')->assertStatus(200)->assertJson(['success' => true]);

        $this->postJson('/api/produk/daftar', ['product' => 'TSH'])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('pesan', ['url_endpoint' => '/api/welcome']);
        $this->assertDatabaseHas('pesan', ['url_endpoint' => '/api/produk/daftar']);
    }
}


