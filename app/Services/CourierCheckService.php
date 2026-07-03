<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CourierCheckService
{
    protected string $baseUrl;
    protected ?string $apiKey;

    public function __construct(?string $apiKey = null, ?string $baseUrl = null)
    {
        $this->baseUrl = rtrim($baseUrl ?? config('services.bdcourier.base_url', 'https://api.bdcourier.com'), '/');
        $this->apiKey = $apiKey ?? config('services.bdcourier.api_key');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Look up a phone number's courier delivery history (success/cancel ratio per courier)
     * via the bdcourier.com Courier Check API.
     */
    public function check(string $phone): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'BD Courier API key is not configured.'];
        }

        try {
            $response = Http::timeout(15)
                ->withToken($this->apiKey)
                ->acceptJson()
                ->post("{$this->baseUrl}/courier-check", [
                    'phone' => $phone,
                ]);
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => 'Could not reach courier check service: ' . $e->getMessage()];
        }

        $body = $response->json();

        if (!$response->successful() || !is_array($body) || ($body['status'] ?? null) !== 'success') {
            $error = is_array($body) ? ($body['message'] ?? $body['error'] ?? null) : null;
            return [
                'success' => false,
                'error' => $error ?? 'Courier check failed (HTTP ' . $response->status() . ').',
            ];
        }

        return [
            'success' => true,
            'data' => $body['data'] ?? [],
            'reports' => $body['reports'] ?? [],
        ];
    }
}
