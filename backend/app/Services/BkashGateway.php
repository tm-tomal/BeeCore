<?php

namespace App\Services;

use App\Models\PaymentGateway;
use App\Models\PaymentGatewayLog;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Real bKash Tokenized Checkout client.
 *
 * The BeeCore platform is the bKash merchant: every online payment collected on
 * the platform (customer invoices + BeeCore SaaS invoices) is charged through
 * this single merchant gateway.
 *
 * Endpoints follow the official bKash "Checkout (URL)" / Tokenized API:
 *  - base:  https://tokenized.{pay|sandbox}.bka.sh/v1.2.0-beta/tokenized/checkout
 *  - token/grant, /create, /execute, /payment/status
 */
class BkashGateway
{
    private const ENDPOINTS = [
        'live' => 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout',
        'sandbox' => 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout',
    ];

    /** API timeout required by bKash — 30 seconds. */
    private const TIMEOUT = 30;

    public static function resolve(): PaymentGateway
    {
        $gateway = PaymentGateway::query()
            ->where('provider', 'bkash')
            ->where('is_active', true)
            ->whereNull('archived_at')
            ->latest()
            ->first();

        if (! $gateway) {
            throw new RuntimeException('No active bKash merchant gateway is configured. Add one under Platform → Payment methods.');
        }

        return $gateway;
    }

    public static function credentials(PaymentGateway $gateway): array
    {
        $credentials = $gateway->credentials ?? [];

        $missing = collect(['app_key', 'app_secret', 'username', 'password'])
            ->reject(fn ($key) => filled($credentials[$key] ?? null));

        if ($missing->isNotEmpty()) {
            throw new RuntimeException('bKash merchant credentials are incomplete. Fill app key, app secret, username and password: '.$missing->implode(', ').'.');
        }

        return $credentials;
    }

    public static function isConfigured(PaymentGateway $gateway): bool
    {
        $credentials = $gateway->credentials ?? [];

        return filled($credentials['app_key'] ?? null)
            && filled($credentials['app_secret'] ?? null)
            && filled($credentials['username'] ?? null)
            && filled($credentials['password'] ?? null);
    }

    private static function baseUrl(PaymentGateway $gateway): string
    {
        return self::ENDPOINTS[$gateway->mode] ?? self::ENDPOINTS['live'];
    }

    /**
     * Grant (and cache for one hour) a bKash id_token for this merchant gateway.
     * bKash asks merchants to reuse the token for its whole lifetime.
     */
    public static function grantToken(PaymentGateway $gateway, bool $fresh = false): string
    {
        $cacheKey = 'bkash_token.'.$gateway->id;

        if (! $fresh) {
            $cached = Cache::get($cacheKey);
            if (is_string($cached) && $cached !== '') {
                return $cached;
            }
        }

        $credentials = self::credentials($gateway);

        $response = Http::timeout(self::TIMEOUT)->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ])->post(self::baseUrl($gateway).'/token/grant', [
            'app_key' => $credentials['app_key'],
            'app_secret' => $credentials['app_secret'],
        ]);

        self::logResponse($gateway, 'token.grant', $response);

        $body = $response->json();

        if (empty($body['id_token'])) {
            throw new RuntimeException('bKash token request failed: '.($body['statusMessage'] ?? 'unexpected response'));
        }

        Cache::put($cacheKey, (string) $body['id_token'], now()->addMinutes(55));

        return (string) $body['id_token'];
    }

    /**
     * Create a bKash Tokenized Checkout session.
     *
     * @return array{paymentID:string, bkashURL:string}
     */
    public static function createPayment(PaymentGateway $gateway, float $amount, string $invoiceNumber, string $callbackUrl): array
    {
        $credentials = self::credentials($gateway);
        $token = self::grantToken($gateway);

        $response = Http::timeout(self::TIMEOUT)->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => $token,
            'X-APP-Key' => $credentials['app_key'],
        ])->post(self::baseUrl($gateway).'/create', [
            'mode' => '0011',
            'payerReference' => $invoiceNumber,
            'callbackURL' => $callbackUrl,
            'amount' => number_format($amount, 2, '.', ''),
            'currency' => 'BDT',
            'intent' => 'sale',
            'merchantInvoiceNumber' => $invoiceNumber,
        ]);

        self::logResponse($gateway, 'payment.create', $response);

        $body = $response->json();

        if (($body['statusCode'] ?? '') !== '0000' || empty($body['bkashURL'])) {
            throw new RuntimeException('bKash could not start this payment: '.($body['statusMessage'] ?? $response->body()));
        }

        return [
            'paymentID' => (string) $body['paymentID'],
            'bkashURL' => (string) $body['bkashURL'],
        ];
    }

    /**
     * Execute a payment after the customer approved it on the bKash page.
     *
     * @return array{statusCode:string, trxID:?string, statusMessage:?string, amount:?string}
     */
    public static function executePayment(PaymentGateway $gateway, string $paymentId): array
    {
        $credentials = self::credentials($gateway);
        $token = self::grantToken($gateway);

        $response = Http::timeout(self::TIMEOUT)->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => $token,
            'X-APP-Key' => $credentials['app_key'],
        ])->post(self::baseUrl($gateway).'/execute', [
            'paymentID' => $paymentId,
        ]);

        self::logResponse($gateway, 'payment.execute', $response);

        $body = $response->json();

        return [
            'statusCode' => (string) ($body['statusCode'] ?? ''),
            'trxID' => $body['trxID'] ?? null,
            'statusMessage' => $body['statusMessage'] ?? null,
            'amount' => isset($body['amount']) ? (string) $body['amount'] : null,
        ];
    }

    /**
     * Query the status of a previously created payment — used to reconcile a
     * payment when the callback or the execute call could not be completed.
     *
     * @return array{statusCode:string, transactionStatus:?string, trxID:?string, statusMessage:?string}
     */
    public static function queryPayment(PaymentGateway $gateway, string $paymentId): array
    {
        $credentials = self::credentials($gateway);
        $token = self::grantToken($gateway);

        $response = Http::timeout(self::TIMEOUT)->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => $token,
            'X-APP-Key' => $credentials['app_key'],
        ])->post(self::baseUrl($gateway).'/payment/status', [
            'paymentID' => $paymentId,
        ]);

        self::logResponse($gateway, 'payment.query', $response);

        $body = $response->json();

        return [
            'statusCode' => (string) ($body['statusCode'] ?? ''),
            'transactionStatus' => $body['transactionStatus'] ?? null,
            'trxID' => $body['trxID'] ?? null,
            'statusMessage' => $body['statusMessage'] ?? null,
        ];
    }

    /**
     * Live connection check used by the Platform → Payment methods console.
     * Returns the raw result; throws with a human message on failure.
     */
    public static function testConnection(PaymentGateway $gateway): array
    {
        if (! self::isConfigured($gateway)) {
            throw new RuntimeException('Missing merchant credentials — enter the app key, app secret, API username and password first.');
        }

        $token = self::grantToken($gateway, fresh: true);
        Cache::forget('bkash_token.'.$gateway->id);

        return [
            'ok' => true,
            'token' => mb_substr($token, 0, 8).'…',
            'mode' => $gateway->mode,
        ];
    }

    private static function log(PaymentGateway $gateway, string $event, string $status, array $metadata = []): void
    {
        PaymentGatewayLog::create([
            'payment_gateway_id' => $gateway->id,
            'event' => $event,
            'status' => $status,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }

    private static function logResponse(PaymentGateway $gateway, string $event, Response $response): void
    {
        $body = json_decode($response->body(), true);
        $statusCode = (string) ($body['statusCode'] ?? ($response->successful() ? '0000' : ''));
        $success = $response->successful() && $statusCode === '0000';

        self::log($gateway, $event, $success ? 'success' : 'failed', [
            'http' => $response->status(),
            'statusCode' => $body['statusCode'] ?? null,
            'statusMessage' => $body['statusMessage'] ?? null,
            'mode' => $gateway->mode,
        ]);
    }
}
