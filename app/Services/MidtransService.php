<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MidtransService
{
    private string $serverKey;

    private string $snapBaseUrl;

    private string $apiBaseUrl;

    public function __construct()
    {
        $this->serverKey = (string) config('services.midtrans.server_key', '');
        $isProduction = (bool) config('services.midtrans.is_production', false);
        $this->snapBaseUrl = $isProduction
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';
        $this->apiBaseUrl = $isProduction
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }

    /**
     * @param  array{
     *   order_id: string,
     *   gross_amount: int,
     *   first_name: string,
     *   email: string,
     *   item_details?: array<int, array{price: int, quantity: int, name: string}>,
     *   metadata?: array<string, mixed>
     * }  $payload
     * @return array<string, mixed>
     *
     * @throws ConnectionException|RequestException
     */
    public function createSnapTransaction(array $payload): array
    {
        $requestPayload = [
            'transaction_details' => [
                'order_id' => $payload['order_id'],
                'gross_amount' => $payload['gross_amount'],
            ],
            'customer_details' => [
                'first_name' => $payload['first_name'],
                'email' => $payload['email'],
            ],
            'item_details' => $payload['item_details'] ?? [
                [
                    'price' => $payload['gross_amount'],
                    'quantity' => 1,
                    'name' => 'Konsultasi Arsitek',
                ],
            ],
            'custom_field1' => isset($payload['metadata'])
                ? json_encode($payload['metadata'])
                : null,
        ];

        $response = Http::withBasicAuth($this->serverKey, '')
            ->acceptJson()
            ->asJson()
            ->post("{$this->snapBaseUrl}/snap/v1/transactions", $requestPayload)
            ->throw();

        /** @var array<string, mixed> $json */
        $json = (array) $response->json();

        return $json;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException|RequestException
     */
    public function fetchTransactionStatus(string $orderId): array
    {
        $response = Http::withBasicAuth($this->serverKey, '')
            ->acceptJson()
            ->get("{$this->apiBaseUrl}/v2/{$orderId}/status")
            ->throw();

        /** @var array<string, mixed> $json */
        $json = (array) $response->json();

        return $json;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException|RequestException
     */
    public function refundTransaction(string $orderId, int $amount, string $reason, ?string $refundKey = null): array
    {
        $refundPayload = [
            'refund_key' => $refundKey ?: 'refund-' . Str::lower(Str::random(10)),
            'amount' => $amount,
            'reason' => $reason,
        ];

        $response = Http::withBasicAuth($this->serverKey, '')
            ->acceptJson()
            ->asJson()
            ->post("{$this->apiBaseUrl}/v2/{$orderId}/refund", $refundPayload)
            ->throw();

        /** @var array<string, mixed> $json */
        $json = (array) $response->json();

        return $json;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function isValidSignature(array $payload): bool
    {
        $signature = (string) ($payload['signature_key'] ?? '');
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');

        if ($signature === '' || $orderId === '' || $statusCode === '' || $grossAmount === '') {
            return false;
        }

        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);

        return hash_equals($expected, $signature);
    }

    public function generateSignatureKey(string $orderId, string $statusCode, string $grossAmount): string
    {
        return hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function mapPaymentStatus(array $payload): string
    {
        $transactionStatus = strtolower((string) ($payload['transaction_status'] ?? ''));
        $fraudStatus = strtolower((string) ($payload['fraud_status'] ?? ''));

        if ($transactionStatus === 'capture') {
            return $fraudStatus === 'accept' ? 'completed' : 'pending';
        }

        return match ($transactionStatus) {
            'settlement' => 'completed',
            'completed' => 'completed',
            'refund', 'partial_refund' => 'completed',
            'pending' => 'pending',
            'expire' => 'expired',
            'cancel', 'deny' => 'failed',
            default => 'pending',
        };
    }
}
