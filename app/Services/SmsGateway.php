<?php

namespace App\Services;

use App\Models\SmsLog;
use App\Models\SmsProvider;
use App\Models\Tenant;
use App\Models\TenantAddon;
use App\Models\TenantSmsBalance;
use Illuminate\Support\Facades\Http;

/**
 * Real SMS delivery through the smsq.global aggregator.
 *
 * The platform holds one smsq.global account (credentials stored encrypted on
 * the active SmsProvider row). Tenants never see these credentials: they send
 * through BeeCore and their TenantSmsBalance wallet is debited per SMS.
 */
class SmsGateway
{
    public const BASE_URL = 'https://api.smsq.global/api/v2';

    /**
     * The active provider used for real delivery (must be type "smsq").
     */
    public static function activeProvider(): ?SmsProvider
    {
        return SmsProvider::query()
            ->where('provider', 'smsq')
            ->where('is_active', true)
            ->whereNull('archived_at')
            ->first();
    }

    /**
     * Query the aggregator balance (BDT).
     */
    public static function providerBalance(?SmsProvider $provider = null): ?float
    {
        $provider ??= self::activeProvider();
        if (! $provider) {
            return null;
        }

        $response = Http::timeout(15)->get(self::BASE_URL.'/Balance', self::authParams($provider));

        return self::dataAmount($response->json()) ?? null;
    }

    /**
     * Fetch registered sender ids from the aggregator.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function providerSenderIds(?SmsProvider $provider = null): array
    {
        $provider ??= self::activeProvider();
        if (! $provider) {
            return [];
        }

        $response = Http::timeout(15)->get(self::BASE_URL.'/SenderId', self::authParams($provider));

        return collect($response->json('Data') ?? [])->all();
    }

    /**
     * Send a single SMS via smsq.global and return the gateway verdict.
     *
     * @return array{ok: bool, provider: ?SmsProvider, recipient: string, message: string, response: mixed, error: string}
     */
    public static function send(string $to, string $message, ?SmsProvider $provider = null): array
    {
        $provider ??= self::activeProvider();
        $recipient = self::normalizeNumber($to);

        if (! $provider) {
            return self::verdict(false, null, $recipient, $message, null, 'No active SMS provider is configured.');
        }

        $params = self::authParams($provider) + [
            'SenderId' => $provider->sender_id ?: ($provider->credentials['sender_id'] ?? ''),
            'MobileNumber' => $recipient,
            'Message' => $message,
        ];

        $response = Http::timeout(20)->asForm()->post(self::BASE_URL.'/SendSMS', $params);
        $payload = $response->json();
        $ok = ($payload['ErrorCode'] ?? null) === 0;

        return self::verdict($ok, $provider, $recipient, $message, $payload, $payload['ErrorDescription'] ?? $response->body());
    }

    /**
     * Credit the tenant SMS wallet when a sold add-on becomes active.
     */
    public static function creditSmsAddon(TenantAddon $assignment): void
    {
        $addon = $assignment->addon;
        if (! $addon || $addon->category !== 'sms') {
            return;
        }

        $credits = (int) ($addon->usage_limit ?? 0);
        if ($credits <= 0) {
            return;
        }

        TenantSmsBalance::query()
            ->firstOrCreate(['tenant_id' => $assignment->tenant_id], ['balance' => 0])
            ->increment('balance', $credits);
    }

    /**
     * Deliver an SMS on behalf of a tenant, debiting their wallet.
     *
     * @return array{ok: bool, reason: ?string, message: string, response: mixed}
     */
    public static function sendForTenant(Tenant $tenant, string $to, string $message): array
    {
        $wallet = TenantSmsBalance::query()->firstOrCreate(['tenant_id' => $tenant->id], ['balance' => 0]);

        if ($wallet->balance < 1) {
            return ['ok' => false, 'reason' => 'insufficient', 'message' => 'The tenant has no SMS credits left.', 'response' => null];
        }

        $result = self::send($to, $message);
        $provider = $result['provider'];

        $status = $result['ok'] ? 'sent' : 'failed';
        $log = SmsLog::create([
            'tenant_id' => $tenant->id,
            'sms_provider_id' => $provider?->id,
            'recipient' => $result['recipient'],
            'message' => $message,
            'status' => $status,
            'cost' => $result['ok'] && $provider ? $provider->price_per_sms : 0,
            'created_at' => now(),
        ]);

        if ($result['ok']) {
            $wallet->decrement('balance', 1);
        }

        return [
            'ok' => $result['ok'],
            'reason' => $result['ok'] ? null : 'gateway',
            'message' => $result['error'] ?: 'SMS sent.',
            'response' => $result['response'],
            'log_id' => $log->id,
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function authParams(SmsProvider $provider): array
    {
        return [
            'ApiKey' => (string) ($provider->credentials['api_key'] ?? ''),
            'ClientId' => (string) ($provider->credentials['client_id'] ?? ''),
        ];
    }

    /**
     * Normalise a Bangladeshi mobile number to the 880… format the gateway expects.
     */
    public static function normalizeNumber(string $number): string
    {
        $digits = preg_replace('/\D+/', '', $number) ?? '';

        if (str_starts_with($digits, '880')) {
            return $digits;
        }
        if (str_starts_with($digits, '0')) {
            return '880'.substr($digits, 1);
        }

        return '880'.$digits;
    }

    private static function dataAmount(?array $payload): ?float
    {
        if (($payload['ErrorCode'] ?? null) !== 0) {
            return null;
        }

        $amount = null;
        foreach (($payload['Data'] ?? []) as $entry) {
            $raw = $entry['Credits'] ?? null;
            if ($raw !== null) {
                $amount = (float) str_replace(['৳', ' '], '', (string) $raw);
            }
        }

        return $amount;
    }

    /**
     * @return array{ok: bool, provider: ?SmsProvider, recipient: string, message: string, response: mixed, error: string}
     */
    private static function verdict(bool $ok, ?SmsProvider $provider, string $recipient, string $message, mixed $response, string $error): array
    {
        return [
            'ok' => $ok,
            'provider' => $provider,
            'recipient' => $recipient,
            'message' => $message,
            'response' => $response,
            'error' => $error,
        ];
    }
}
