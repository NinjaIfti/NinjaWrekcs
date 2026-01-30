<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsNetBdService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.sms.net.bd';

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? config('services.sms_net_bd.api_key', '');
    }

    /**
     * Fetch account balance from SMS.net.bd
     */
    public function getBalance(): array
    {
        if (!$this->apiKey) {
            return ['success' => false, 'balance' => null, 'error' => 'SMS API key not configured.'];
        }

        $response = Http::get("{$this->baseUrl}/user/balance/", [
            'api_key' => $this->apiKey,
        ]);

        $data = $response->json();
        if ($response->successful() && isset($data['error']) && (int) $data['error'] === 0) {
            return [
                'success' => true,
                'balance' => $data['data']['balance'] ?? '0',
                'msg' => $data['msg'] ?? 'Success',
            ];
        }

        return [
            'success' => false,
            'balance' => null,
            'error' => $data['msg'] ?? $response->body() ?? 'Unknown error',
        ];
    }

    /**
     * Send SMS to one or more numbers (comma-separated).
     * Numbers should be in 880XXXXXXXXXX or 01XXXXXXXXX format.
     */
    public function sendSms(string $msg, string $to, ?string $schedule = null, ?string $senderId = null): array
    {
        if (!$this->apiKey) {
            return ['success' => false, 'request_id' => null, 'error' => 'SMS API key not configured.'];
        }

        $payload = [
            'api_key' => $this->apiKey,
            'msg' => $msg,
            'to' => $to,
        ];
        if ($schedule) {
            $payload['schedule'] = $schedule;
        }
        if ($senderId) {
            $payload['sender_id'] = $senderId;
        }

        $response = Http::asForm()->post("{$this->baseUrl}/sendsms", $payload);
        $data = $response->json();

        if ($response->successful() && isset($data['error']) && (int) $data['error'] === 0) {
            return [
                'success' => true,
                'request_id' => $data['data']['request_id'] ?? null,
                'msg' => $data['msg'] ?? 'Request successfully submitted',
            ];
        }

        return [
            'success' => false,
            'request_id' => null,
            'error' => $data['msg'] ?? $response->body() ?? 'Unknown error',
        ];
    }

    /**
     * Normalize Bangladesh phone to 880XXXXXXXXXX
     */
    public static function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (str_starts_with($phone, '880')) {
            return $phone;
        }
        if (str_starts_with($phone, '0')) {
            return '88' . $phone;
        }
        return '880' . $phone;
    }
}
