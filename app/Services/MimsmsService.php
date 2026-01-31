<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MimsmsService
{
    protected string $username;
    protected string $apiKey;
    protected string $senderName;
    protected ?string $campaignId;
    protected string $baseUrl = 'https://api.mimsms.com';

    public function __construct(?string $username = null, ?string $apiKey = null, ?string $senderName = null, ?string $campaignId = null)
    {
        $this->username = $username ?? config('services.mimsms.username', '');
        $this->apiKey = $apiKey ?? config('services.mimsms.api_key', '');
        $this->senderName = $senderName ?? config('services.mimsms.sender_name', '');
        $this->campaignId = $campaignId ?? config('services.mimsms.campaign_id');
    }

    public function isConfigured(): bool
    {
        return $this->username !== '' && $this->apiKey !== '' && $this->senderName !== '';
    }

    /**
     * Fetch account balance from MiMSMS balanceCheck API (POST JSON).
     */
    public function getBalance(): array
    {
        if ($this->username === '' || $this->apiKey === '') {
            return ['success' => false, 'balance' => null, 'error' => 'MiMSMS username and API key required for balance check.'];
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['Content-Type' => 'application/json', 'Accept' => 'application/json'])
                ->post("{$this->baseUrl}/api/SmsSending/balanceCheck", [
                    'UserName' => $this->username,
                    'Apikey' => $this->apiKey,
                ]);
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'balance' => null,
                'error' => 'Could not reach SMS service: ' . $e->getMessage(),
            ];
        }

        $data = $response->json() ?? [];
        $statusCode = $data['statusCode'] ?? (string) $response->status();
        $status = $data['status'] ?? null;
        $balance = $data['responseResult'] ?? null;

        $ok = $response->successful() && ($statusCode === '200' || $statusCode === 200) && ($status === 'Ok' || $balance !== null);
        if ($ok && $balance !== null && $balance !== '') {
            return [
                'success' => true,
                'balance' => is_string($balance) ? $balance : (string) $balance,
                'msg' => 'Ok',
            ];
        }

        $error = $data['message'] ?? $data['Message'] ?? $response->body() ?? 'Could not fetch balance.';
        return [
            'success' => false,
            'balance' => null,
            'error' => is_string($error) ? $error : (string) json_encode($error),
        ];
    }

    /** Max numbers per OneToMany request (chunk size for bulk). */
    protected int $bulkChunkSize = 500;

    /**
     * Send SMS to one or more numbers (bulk). Uses MiMSMS One-to-Many API.
     * Large lists are chunked (e.g. 500 per request); all chunks are sent and results combined.
     * MobileNumber: comma-separated, international format without + (e.g. 88018xxxxxxxx,88017xxxxxxxx).
     */
    public function sendSms(string $msg, string $to, string $transactionType = 'T'): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'trxn_ids' => [], 'error' => 'MiMSMS is not configured (username, API key, sender name).'];
        }

        $numbers = array_filter(array_unique(array_map('trim', explode(',', $to))));
        if (empty($numbers)) {
            return ['success' => false, 'trxn_ids' => [], 'error' => 'No valid mobile numbers provided.'];
        }

        $chunks = array_chunk($numbers, $this->bulkChunkSize);
        $allTrxnIds = [];
        $totalSent = 0;
        $firstError = null;

        foreach ($chunks as $chunk) {
            $mobileNumber = implode(',', $chunk);
            $payload = [
                'UserName' => $this->username,
                'Apikey' => $this->apiKey,
                'MobileNumber' => $mobileNumber,
                'CampaignId' => ($transactionType === 'P' && $this->campaignId) ? $this->campaignId : 'null',
                'SenderName' => $this->senderName,
                'TransactionType' => $transactionType,
                'Message' => $msg,
            ];

            try {
                $response = Http::timeout(30)
                    ->withHeaders(['Content-Type' => 'application/json', 'Accept' => 'application/json'])
                    ->post("{$this->baseUrl}/api/SmsSending/OneToMany", $payload);
            } catch (\Throwable $e) {
                $firstError = $firstError ?? ('Could not reach SMS service: ' . $e->getMessage());
                continue;
            }

            $data = $response->json() ?? [];
            $statusCode = $data['statusCode'] ?? (string) $response->status();
            $status = $data['status'] ?? null;
            $trxnId = $data['trxnId'] ?? $data['trxn_id'] ?? null;
            $responseResult = $data['responseResult'] ?? $data['message'] ?? $data['Message'] ?? null;

            $ok = $response->successful() && ($statusCode === '200' || $statusCode === 200) && ($status === 'Success' || $trxnId !== null);
            if ($ok && $trxnId !== null && $trxnId !== '') {
                $allTrxnIds[] = is_string($trxnId) ? $trxnId : (string) $trxnId;
                $totalSent += count($chunk);
            } else {
                $firstError = $firstError ?? ($responseResult ?? $data['message'] ?? $data['Message'] ?? $response->body() ?? 'Unknown error');
            }
        }

        if ($totalSent > 0) {
            return [
                'success' => true,
                'trxn_ids' => $allTrxnIds,
                'sent_count' => $totalSent,
                'failed_count' => count($numbers) - $totalSent,
                'response_result' => $firstError,
            ];
        }

        return [
            'success' => false,
            'trxn_ids' => [],
            'error' => is_string($firstError ?? '') ? $firstError : (string) json_encode($firstError ?? 'No messages could be sent.'),
        ];
    }

    /**
     * Normalize Bangladesh phone to 880XXXXXXXXXX (for storage/display consistency).
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
