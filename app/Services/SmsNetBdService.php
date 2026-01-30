<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsNetBdService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.sms.net.bd';

    /** Common and Send SMS error codes from SMS.net.bd API */
    protected const ERROR_MESSAGES = [
        0 => 'Success',
        400 => 'Invalid or missing parameter. Check API key, message, and recipient numbers.',
        403 => 'You do not have permission to perform this request.',
        404 => 'The requested resource was not found.',
        405 => 'Authorization required. Check your API key.',
        409 => 'Server error. Please try again later.',
        410 => 'SMS account expired. Recharge or renew your account.',
        411 => 'Reseller account expired or suspended.',
        412 => 'Invalid schedule date or time. Use format Y-m-d H:i:s.',
        413 => 'Invalid Sender ID. Use an approved Sender ID.',
        414 => 'Message is empty.',
        415 => 'Message is too long.',
        416 => 'No valid recipient number found. Check phone format (880XXXXXXXXXX).',
        417 => 'Insufficient balance. Recharge your SMS account.',
        420 => 'Content blocked. Message may violate provider rules.',
        421 => 'You can only send SMS to your registered phone until first balance recharge.',
    ];

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? config('services.sms_net_bd.api_key', '');
    }

    /**
     * Resolve user-friendly error message from API response.
     */
    protected function errorMessage(array $data, $response, string $fallback = 'SMS request failed. Please try again.'): string
    {
        $code = isset($data['error']) ? (int) $data['error'] : null;
        if ($code !== null && array_key_exists($code, self::ERROR_MESSAGES)) {
            return self::ERROR_MESSAGES[$code];
        }
        if (!empty($data['msg']) && is_string($data['msg'])) {
            return $data['msg'];
        }
        if (!$response->successful()) {
            return 'Network or server error (' . $response->status() . '). ' . $fallback;
        }
        return $fallback;
    }

    /**
     * Fetch account balance from SMS.net.bd
     */
    public function getBalance(): array
    {
        if (!$this->apiKey) {
            return ['success' => false, 'balance' => null, 'error' => 'SMS API key not configured.'];
        }

        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/user/balance/", [
                'api_key' => $this->apiKey,
            ]);
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'balance' => null,
                'error' => 'Could not reach SMS service. ' . $e->getMessage(),
            ];
        }

        $data = $response->json() ?? [];
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
            'error' => $this->errorMessage($data, $response, 'Could not fetch balance.'),
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

        try {
            $response = Http::timeout(15)->asForm()->post("{$this->baseUrl}/sendsms", $payload);
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'request_id' => null,
                'error' => 'Could not reach SMS service. ' . $e->getMessage(),
            ];
        }

        $data = $response->json() ?? [];
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
            'error' => $this->errorMessage($data, $response, 'SMS could not be sent. Please try again.'),
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
