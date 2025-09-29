<?php

namespace App\Services;

use App\Models\QontakResponse;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class QontakService
{
    private function getServiceConfig(string $serviceType): array
    {
        $configs = [
            'klaim' => [
                'template_id' => '1df5bb8c-abd8-4aa2-aeff-493441431094',
                'required_fields' => ['nama_peserta', 'nomor_wa_tujuan', 'nama_produk', 'nomor_id_claim', 'nomor_rekening'],
                'payload_mapping' => [
                    ['key' => '1', 'field' => 'nama_peserta', 'value' => 'customer_name'],
                    ['key' => '2', 'field' => 'nama_produk', 'value' => 'product_name'],
                    ['key' => '3', 'field' => 'nomor_id_claim', 'value' => 'claim_id'],
                    ['key' => '4', 'field' => 'nomor_rekening', 'value' => 'account_number'],
                ]
            ],
            'thl' => [
                'template_id' => 'a84ddd60-8082-4f5b-a585-a8748e7ecf64',
                'required_fields' => ['nama_peserta', 'nomor_wa_tujuan', 'produk_asuransi', 'nomor_polis', 'nomor_va', 'premi_per_bulan', 'periode_tagihan'],
                'payload_mapping' => [
                    ['key' => '1', 'field' => 'nama_peserta', 'value' => 'customer_name'],
                    ['key' => '2', 'field' => 'produk_asuransi', 'value' => 'product_name'],
                    ['key' => '3', 'field' => 'nomor_polis', 'value' => 'policy_number'],
                    ['key' => '4', 'field' => 'nomor_va', 'value' => 'virtual_account'],
                    ['key' => '5', 'field' => 'premi_per_bulan', 'value' => 'premium_amount', 'format' => 'currency'],
                    ['key' => '6', 'field' => 'periode_tagihan', 'value' => 'billing_period'],
                ]
            ]
        ];
        
        return $configs[$serviceType] ?? [];
    }

    private function formatValue($value, $format = null): string
    {
        switch ($format) {
            case 'currency':
                return number_format($value, 0, ',', '.');
            default:
                return (string) $value;
        }
    }

    private function sendWhatsAppWithConfig(array $payloadInput, string $serviceType): array
    {
        try {
            $config = $this->getServiceConfig($serviceType);
            if (empty($config)) {
                return ['success' => false, 'error' => "Unknown service type: {$serviceType}"];
            }

            $baseUrl = 'https://service-chat.qontak.com/api/open/v1';
            $tokenResult = $this->getAccessToken();
            if (!$tokenResult['success']) {
                return $tokenResult;
            }
            $bearerToken = $tokenResult['access_token'];
            $channelIntegrationId = '3702ae75-4d97-482c-969a-49f19254c418';
            // Allow per-call override of template ID via payload
            $messageTemplateId = $payloadInput['message_template_id']
                ?? ($payloadInput['template_id'] ?? $config['template_id']);

            if (empty($bearerToken)) {
                Log::error('Qontak bearer token is not configured');
                return ['success' => false, 'error' => 'Qontak bearer token is not configured', 'message' => 'Configuration error: Missing bearer token'];
            }
            if (empty($messageTemplateId)) {
                Log::error('Qontak message template ID is not configured');
                return ['success' => false, 'error' => 'Qontak message template ID is not configured', 'message' => 'Configuration error: Missing message template ID'];
            }

            // Validate required fields
            foreach ($config['required_fields'] as $field) {
                if (empty($payloadInput[$field])) {
                    Log::error("Required field '{$field}' is empty", ['payloadInput' => $payloadInput]);
                    return ['success' => false, 'error' => "Required field '{$field}' is empty", 'message' => "Validation error: Missing required field '{$field}'"];
                }
            }

            // Build parameters based on config
            $parameters = [];
            foreach ($config['payload_mapping'] as $mapping) {
                $value = $payloadInput[$mapping['field']];
                if (isset($mapping['format'])) {
                    $value = $this->formatValue($value, $mapping['format']);
                }
                $parameters[] = [
                    'key' => $mapping['key'],
                    'value_text' => $value,
                    'value' => $mapping['value']
                ];
            }

            $payload = [
                'to_name' => $payloadInput['nama_peserta'],
                'to_number' => $this->normalizeIndonesianMsisdn($payloadInput['nomor_wa_tujuan']),
                'message_template_id' => $messageTemplateId,
                'channel_integration_id' => $channelIntegrationId,
                'language' => ['code' => 'id'],
                'parameters' => ['body' => $parameters]
            ];

            Log::info("Sending {$serviceType} WhatsApp via Qontak", [
                'payload' => $payload,
                'config' => [
                    'base_url' => $baseUrl,
                    'channel_integration_id' => $channelIntegrationId,
                    'message_template_id' => $messageTemplateId,
                    'bearer_token_length' => strlen($bearerToken)
                ]
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $bearerToken
            ])->post($baseUrl . '/broadcasts/whatsapp/direct', $payload);

            Log::info("Qontak response ({$serviceType})", ['response' => $response->body()]);

            if ($response->status() === 401) {
                Log::warning("Qontak responded 401 ({$serviceType}). Attempting token refresh and retry.");
                $settings = Setting::query()->first();
                $refreshToken = $settings?->refresh_token;
                if (!empty($refreshToken)) {
                    $refreshResult = $this->refreshToken($refreshToken);
                    if ($refreshResult['success']) {
                        $settings->access_token = $refreshResult['access_token'];
                        if (!empty($refreshResult['refresh_token'])) { $settings->refresh_token = $refreshResult['refresh_token']; }
                        if (!empty($refreshResult['expires_at'])) { $settings->token_expires_at = $refreshResult['expires_at']; }
                        if (Schema::hasColumn($settings->getTable(), 'token')) { $settings->token = $refreshResult['access_token']; }
                        if (Schema::hasColumn($settings->getTable(), 'expires_at') && empty($settings->token_expires_at)) { $settings->expires_at = $refreshResult['expires_at']; }
                        $settings->save();

                        $bearerToken = $refreshResult['access_token'];
                        $response = Http::withHeaders([
                            'Authorization' => 'Bearer ' . $bearerToken
                        ])->post($baseUrl . '/broadcasts/whatsapp/direct', $payload);
                    } else {
                        Log::error("Token refresh after 401 failed ({$serviceType})", $refreshResult);
                    }
                } else {
                    Log::error("Cannot refresh: refresh_token is missing in settings ({$serviceType})");
                }
            }

            try {
                QontakResponse::create([
                    'endpoint' => '/broadcasts/whatsapp/direct',
                    'to_number' => $payload['to_number'] ?? null,
                    'to_name' => $payload['to_name'] ?? null,
                    'message_template_id' => $messageTemplateId,
                    'channel_integration_id' => $channelIntegrationId,
                    'http_status' => $response->status(),
                    'request_payload' => $payload,
                    'response_body' => json_decode($response->body(), true) ?? $response->body(),
                ]);
            } catch (\Throwable $e) {
                Log::warning("Failed to store Qontak response ({$serviceType})", ['error' => $e->getMessage()]);
            }

            if ($response->successful()) {
                Log::info("{$serviceType} WhatsApp sent successfully", ['response' => $response->json()]);
                return ['success' => true, 'data' => $response->json(), 'message' => "WhatsApp {$serviceType} sent successfully"];
            }

            $errorResponse = $response->body();
            $errorData = null;
            try { $errorData = json_decode($errorResponse, true); } catch (\Exception $e) {}
            Log::error("Failed to send WhatsApp {$serviceType}", [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'response_body' => $errorResponse,
                'parsed_error' => $errorData,
                'payload_sent' => $payload,
                'config_used' => [
                    'base_url' => $baseUrl,
                    'channel_integration_id' => $channelIntegrationId,
                    'message_template_id' => $messageTemplateId,
                    'bearer_token_length' => strlen($bearerToken)
                ]
            ]);
            return ['success' => false, 'error' => $errorResponse, 'error_parsed' => $errorData, 'status' => $response->status(), 'message' => "Failed to send WhatsApp {$serviceType}"];
        } catch (\Exception $e) {
            Log::error("Exception occurred while sending WhatsApp {$serviceType}", ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return ['success' => false, 'error' => $e->getMessage(), 'message' => "Exception occurred while sending WhatsApp {$serviceType}"];
        }
    }

    // Removed specific send methods; use sendDirect with controller-built payload

    public function sendDirect(array $payloadInput): array
    {
        try {
            $baseUrl = 'https://service-chat.qontak.com/api/open/v1';
            $tokenResult = $this->getAccessToken();
            if (!$tokenResult['success']) {
                return $tokenResult;
            }
            $bearerToken = $tokenResult['access_token'];

            if (empty($bearerToken)) {
                Log::error('Qontak bearer token is not configured');
                return ['success' => false, 'error' => 'Qontak bearer token is not configured', 'message' => 'Configuration error: Missing bearer token'];
            }

            // Use payload exactly from controller, with a default channel if not provided
            $payload = [
                'to_name' => $payloadInput['to_name'] ?? null,
                'to_number' => $payloadInput['to_number'] ?? null,
                'message_template_id' => $payloadInput['message_template_id'] ?? ($payloadInput['template_id'] ?? null),
                'channel_integration_id' => $payloadInput['channel_integration_id'] ?? '3702ae75-4d97-482c-969a-49f19254c418',
                'language' => $payloadInput['language'] ?? ['code' => 'id'],
                'parameters' => $payloadInput['parameters'] ?? ['body' => []],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $bearerToken
            ])->post($baseUrl . '/broadcasts/whatsapp/direct', $payload);

            Log::info('Qontak response (direct)', ['response' => $response->body()]);

            if ($response->status() === 401) {
                Log::warning('Qontak responded 401 (direct). Attempting token refresh and retry.');
                $settings = Setting::query()->first();
                $refreshToken = $settings?->refresh_token;
                if (!empty($refreshToken)) {
                    $refreshResult = $this->refreshToken($refreshToken);
                    if ($refreshResult['success']) {
                        $settings->access_token = $refreshResult['access_token'];
                        if (!empty($refreshResult['refresh_token'])) { $settings->refresh_token = $refreshResult['refresh_token']; }
                        if (!empty($refreshResult['expires_at'])) { $settings->token_expires_at = $refreshResult['expires_at']; }
                        if (Schema::hasColumn($settings->getTable(), 'token')) { $settings->token = $refreshResult['access_token']; }
                        if (Schema::hasColumn($settings->getTable(), 'expires_at') && empty($settings->token_expires_at)) { $settings->expires_at = $refreshResult['expires_at']; }
                        $settings->save();

                        $bearerToken = $refreshResult['access_token'];
                        $response = Http::withHeaders([
                            'Authorization' => 'Bearer ' . $bearerToken
                        ])->post($baseUrl . '/broadcasts/whatsapp/direct', $payload);
                    } else {
                        Log::error('Token refresh after 401 failed (direct)', $refreshResult);
                    }
                } else {
                    Log::error('Cannot refresh: refresh_token is missing in settings (direct)');
                }
            }

            try {
                QontakResponse::create([
                    'endpoint' => '/broadcasts/whatsapp/direct',
                    'to_number' => $payload['to_number'] ?? null,
                    'to_name' => $payload['to_name'] ?? null,
                    'message_template_id' => $payload['message_template_id'] ?? null,
                    'channel_integration_id' => $payload['channel_integration_id'] ?? null,
                    'http_status' => $response->status(),
                    'request_payload' => $payload,
                    'response_body' => json_decode($response->body(), true) ?? $response->body(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Failed to store Qontak response (direct)', ['error' => $e->getMessage()]);
            }

            if ($response->successful()) {
                Log::info('Direct WhatsApp sent successfully', [ 'response' => $response->json() ]);
                return [ 'success' => true, 'data' => $response->json(), 'message' => 'WhatsApp sent successfully' ];
            }

            $errorResponse = $response->body();
            $errorData = null;
            try { $errorData = json_decode($errorResponse, true); } catch (\Exception $e) {}
            Log::error('Failed to send WhatsApp (direct)', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'response_body' => $errorResponse,
                'parsed_error' => $errorData,
                'payload_sent' => $payload,
            ]);
            return [ 'success' => false, 'error' => $errorResponse, 'error_parsed' => $errorData, 'status' => $response->status(), 'message' => 'Failed to send WhatsApp' ];
        } catch (\Exception $e) {
            Log::error('Exception occurred while sending WhatsApp (direct)', [ 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString() ]);
            return [ 'success' => false, 'error' => $e->getMessage(), 'message' => 'Exception occurred while sending WhatsApp' ];
        }
    }
    public function sendWhatsAppDirect(array $claimData): array
    {
        try {
            $baseUrl = 'https://service-chat.qontak.com/api/open/v1';
            $tokenResult = $this->getAccessToken();
            if (!$tokenResult['success']) {
                return $tokenResult;
            }
            $bearerToken = $tokenResult['access_token'];
            $channelIntegrationId = '3702ae75-4d97-482c-969a-49f19254c418';
            $messageTemplateId = '1df5bb8c-abd8-4aa2-aeff-493441431094';

            if (empty($bearerToken)) {
                Log::error('Qontak bearer token is not configured');
                return [
                    'success' => false,
                    'error' => 'Qontak bearer token is not configured',
                    'message' => 'Configuration error: Missing bearer token'
                ];
            }
            if (empty($messageTemplateId)) {
                Log::error('Qontak message template ID is not configured');
                return [
                    'success' => false,
                    'error' => 'Qontak message template ID is not configured',
                    'message' => 'Configuration error: Missing message template ID'
                ];
            }

            $requiredFields = ['nama_peserta', 'nomor_wa_tujuan', 'nama_produk', 'nomor_id_claim', 'nomor_rekening'];
            foreach ($requiredFields as $field) {
                if (empty($claimData[$field])) {
                    Log::error("Required field '{$field}' is empty", ['claimData' => $claimData]);
                    return [
                        'success' => false,
                        'error' => "Required field '{$field}' is empty",
                        'message' => "Validation error: Missing required field '{$field}'"
                    ];
                }
            }

            $payload = [
                'to_name' => $claimData['nama_peserta'],
                'to_number' => $this->normalizeIndonesianMsisdn($claimData['nomor_wa_tujuan']),
                'message_template_id' => $messageTemplateId,
                'channel_integration_id' => $channelIntegrationId,
                'language' => [ 'code' => 'id' ],
                'parameters' => [
                    'body' => [
                        [ 'key' => '1', 'value_text' => $claimData['nama_peserta'], 'value' => 'customer_name' ],
                        [ 'key' => '2', 'value_text' => $claimData['nama_produk'], 'value' => 'product_name' ],
                        [ 'key' => '3', 'value_text' => $claimData['nomor_id_claim'], 'value' => 'claim_id' ],
                        [ 'key' => '4', 'value_text' => $claimData['nomor_rekening'], 'value' => 'account_number' ],
                    ]
                ]
            ];

            Log::info('Sending WhatsApp message via Qontak', [
                'payload' => $payload,
                'config' => [
                    'base_url' => $baseUrl,
                    'channel_integration_id' => $channelIntegrationId,
                    'message_template_id' => $messageTemplateId,
                    'bearer_token_length' => strlen($bearerToken)
                ]
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $bearerToken
            ])->post($baseUrl . '/broadcasts/whatsapp/direct', $payload);

            Log::info('Qontak response', ['response' => $response->body()]);

            if ($response->status() === 401) {
                Log::warning('Qontak responded 401. Attempting token refresh and retry.');
                $settings = Setting::query()->first();
                $refreshToken = $settings?->refresh_token;
                if (!empty($refreshToken)) {
                    $refreshResult = $this->refreshToken($refreshToken);
                    if ($refreshResult['success']) {
                        $settings->access_token = $refreshResult['access_token'];
                        if (!empty($refreshResult['refresh_token'])) {
                            $settings->refresh_token = $refreshResult['refresh_token'];
                        }
                        if (!empty($refreshResult['expires_at'])) {
                            $settings->token_expires_at = $refreshResult['expires_at'];
                        }
                        if (Schema::hasColumn($settings->getTable(), 'token')) {
                            $settings->token = $refreshResult['access_token'];
                        }
                        if (Schema::hasColumn($settings->getTable(), 'expires_at') && empty($settings->token_expires_at)) {
                            $settings->expires_at = $refreshResult['expires_at'];
                        }
                        $settings->save();

                        $bearerToken = $refreshResult['access_token'];
                        $response = Http::withHeaders([
                            'Authorization' => 'Bearer ' . $bearerToken
                        ])->post($baseUrl . '/broadcasts/whatsapp/direct', $payload);
                    } else {
                        Log::error('Token refresh after 401 failed', $refreshResult);
                    }
                } else {
                    Log::error('Cannot refresh: refresh_token is missing in settings');
                }
            }

            try {
                QontakResponse::create([
                    'endpoint' => '/broadcasts/whatsapp/direct',
                    'to_number' => $payload['to_number'] ?? null,
                    'to_name' => $payload['to_name'] ?? null,
                    'message_template_id' => $messageTemplateId,
                    'channel_integration_id' => $channelIntegrationId,
                    'http_status' => $response->status(),
                    'request_payload' => $payload,
                    'response_body' => json_decode($response->body(), true) ?? $response->body(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Failed to store Qontak response', ['error' => $e->getMessage()]);
            }

            if ($response->successful()) {
                Log::info('WhatsApp message sent successfully', [ 'response' => $response->json() ]);
                return [ 'success' => true, 'data' => $response->json(), 'message' => 'WhatsApp message sent successfully' ];
            }

            $errorResponse = $response->body();
            $errorData = null;
            try { $errorData = json_decode($errorResponse, true); } catch (\Exception $e) {}
            Log::error('Failed to send WhatsApp message', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'response_body' => $errorResponse,
                'parsed_error' => $errorData,
                'payload_sent' => $payload,
                'config_used' => [
                    'base_url' => $baseUrl,
                    'channel_integration_id' => $channelIntegrationId,
                    'message_template_id' => $messageTemplateId,
                    'bearer_token_length' => strlen($bearerToken)
                ]
            ]);
            return [
                'success' => false,
                'error' => $errorResponse,
                'error_parsed' => $errorData,
                'status' => $response->status(),
                'message' => 'Failed to send WhatsApp message'
            ];
        } catch (\Exception $e) {
            Log::error('Exception occurred while sending WhatsApp message', [ 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString() ]);
            return [ 'success' => false, 'error' => $e->getMessage(), 'message' => 'Exception occurred while sending WhatsApp message' ];
        }
    }

    public function getAccessToken(): array
    {
        try {
            $settings = Setting::query()->first();
            if (!$settings) {
                Log::error('Settings row not found');
                return [ 'success' => false, 'message' => 'Settings not found' ];
            }

            $accessToken = $settings->access_token ?? $settings->token ?? null;
            $refreshToken = $settings->refresh_token ?? null;
            $expiresAtRaw = $settings->token_expires_at ?? $settings->expires_at ?? null;

            $now = Carbon::now();
            $expiresAt = null;
            if (!empty($expiresAtRaw)) {
                try { $expiresAt = Carbon::parse($expiresAtRaw); } catch (\Throwable $e) { Log::warning('Unable to parse token expiration timestamp', ['raw' => $expiresAtRaw]); }
            }

            $needsRefresh = empty($accessToken) || empty($refreshToken) || ($expiresAt instanceof Carbon && $now->diffInSeconds($expiresAt, false) <= 60);
            if ($needsRefresh) {
                if (empty($refreshToken)) {
                    return [ 'success' => false, 'message' => 'Refresh token not available in settings' ];
                }
                $refreshResult = $this->refreshToken($refreshToken);
                if (!$refreshResult['success']) { return $refreshResult; }

                $settings->access_token = $refreshResult['access_token'];
                if (!empty($refreshResult['refresh_token'])) { $settings->refresh_token = $refreshResult['refresh_token']; }
                if (!empty($refreshResult['expires_at'])) { $settings->token_expires_at = $refreshResult['expires_at']; }
                if (Schema::hasColumn($settings->getTable(), 'token')) { $settings->token = $refreshResult['access_token']; }
                if (Schema::hasColumn($settings->getTable(), 'expires_at') && empty($settings->token_expires_at)) { $settings->expires_at = $refreshResult['expires_at']; }
                $settings->save();

                return [ 'success' => true, 'access_token' => $refreshResult['access_token'] ];
            }

            return [ 'success' => true, 'access_token' => $accessToken ];
        } catch (\Throwable $e) {
            Log::error('Failed to get Qontak access token', ['error' => $e->getMessage()]);
            return [ 'success' => false, 'message' => 'Failed to get access token' ];
        }
    }

    public function refreshToken(string $refreshToken): array
    {
        try {
            $oauthUrl = 'https://service-chat.qontak.com/oauth/token';
            $clientId = 'RRrn6uIxalR_QaHFlcKOqbjHMG63elEdPTair9B9YdY';
            $clientSecret = 'Sa8IGIh_HpVK1ZLAF0iFf7jU760osaUNV659pBIZR00';

            $payload = [
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ];

            $response = Http::asJson()->post($oauthUrl, $payload);
            if (!$response->successful()) {
                Log::error('Token refresh failed', [ 'status' => $response->status(), 'body' => $response->body() ]);
                return [ 'success' => false, 'message' => 'Token refresh request failed', 'status' => $response->status(), 'body' => $response->body() ];
            }

            $data = $response->json();
            $accessToken = $data['access_token'] ?? null;
            $newRefreshToken = $data['refresh_token'] ?? null;
            $expiresIn = $data['expires_in'] ?? null; // seconds
            $expiresAt = null;
            if (!empty($expiresIn)) { $expiresAt = Carbon::now()->addSeconds(intval($expiresIn))->toDateTimeString(); }

            if (empty($accessToken)) { return [ 'success' => false, 'message' => 'Token refresh succeeded but access_token missing' ]; }

            return [ 'success' => true, 'access_token' => $accessToken, 'refresh_token' => $newRefreshToken, 'expires_at' => $expiresAt ];
        } catch (\Throwable $e) {
            Log::error('Exception during token refresh', [ 'error' => $e->getMessage() ]);
            return [ 'success' => false, 'message' => 'Exception during token refresh' ];
        }
    }

    public function normalizeIndonesianMsisdn($input): string
    {
        $number = trim((string) $input);
        if ($number === '') { return $number; }
        $number = preg_replace('/[\s\-()]/', '', $number);
        if ($number === null) { $number = ''; }
        if (strpos($number, '+') === 0) { $number = substr($number, 1); }
        $number = preg_replace('/\D/', '', $number) ?? '';
        if ($number === '') { return $number; }
        if (strpos($number, '62') === 0) { return $number; }
        if (strpos($number, '0') === 0) { return '62' . substr($number, 1); }
        if (strpos($number, '8') === 0) { return '62' . $number; }
        return $number;
    }
}


