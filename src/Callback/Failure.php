<?php

declare(strict_types=1);

namespace OpenRuntimes\Orchestrator\Callback;

use OpenRuntimes\Orchestrator\Exception\ClientException;

/**
 * The error carried by a failed callback: a stable snake_case code to branch
 * on, and a sentence about this occurrence to show — never to parse.
 */
final readonly class Failure
{
    public function __construct(
        public string $code,
        public string $message,
    ) {}

    /**
     * The failure a callback's data reports, or null when it reports success.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromData(array $data): ?self
    {
        return \array_key_exists('error', $data) ? self::fromValue($data['error']) : null;
    }

    /**
     * Orchestrator 2.2 sends `{code, message}`; a 2.1 orchestrator mid-upgrade
     * still sends the bare code as a string, so that is accepted with no message.
     */
    private static function fromValue(mixed $error): self
    {
        if (\is_string($error) && $error !== '') {
            return new self($error, '');
        }
        if (! \is_array($error) || ! \is_string($error['code'] ?? null) || $error['code'] === '') {
            throw new ClientException('Invalid callback error: missing string code.');
        }

        $message = $error['message'] ?? '';
        if (! \is_string($message)) {
            throw new ClientException('Invalid callback error: message must be a string.');
        }

        return new self($error['code'], $message);
    }
}
