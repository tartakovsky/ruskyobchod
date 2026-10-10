<?php
declare(strict_types=1);
namespace Gastronom\OrderReview;

/** A signed, expiring capability for one recipient and one proposal. No side effects. */
final class Consent
{
    public static function token(int $id, array $s, string $email, int $expires, string $secret): string
    {
        $binding = json_encode([$id, $s['revision'], strtolower(trim($email)), $expires,
            $s['quote'], $s['cause'], $s['comment'], $s['language']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return hash_hmac('sha256', $binding, $secret);
    }

    public static function valid(int $id, array $s, string $email, int $expires, string $token, string $secret, int $now): bool
    {
        $entry = $s['mail']['proposal-' . $s['revision']] ?? [];
        return $id > 0 && $email !== '' && $expires > $now
            && ($entry['approval_expires'] ?? 0) === $expires
            && ($entry['status'] ?? '') === 'sent' && $s['sent_revision'] === $s['revision']
            && in_array($s['status'], ['awaiting', 'agreed'], true)
            && preg_match('/^[a-f0-9]{64}$/D', $token) === 1
            && hash_equals(self::token($id, $s, $email, $expires, $secret), $token);
    }
}
