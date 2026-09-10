<?php

declare(strict_types=1);

namespace OpenRuntimes\Orchestrator\Callback;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use OpenRuntimes\Orchestrator\Exception\ClientException;

/**
 * A CloudEvents 1.0 envelope. It carries data without knowing what the data
 * means: decode() hands the type and raw data to whatever does.
 *
 * @template T
 */
final readonly class CloudEvent
{
    /**
     * @param  T  $data
     */
    public function __construct(
        public string $specVersion,
        public string $type,
        public string $source,
        public string $subject,
        public string $id,
        public DateTimeInterface $time,
        public string $dataContentType,
        public mixed $data,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return self<array<string, mixed>>
     */
    public static function fromArray(array $payload): self
    {
        return self::decode($payload, static fn (string $type, array $data): array => $data);
    }

    /**
     * @template U
     *
     * @param  array<string, mixed>  $payload
     * @param  callable(string, array<string, mixed>): U  $decode
     * @return self<U>
     */
    public static function decode(array $payload, callable $decode): self
    {
        $data = $payload['data'] ?? [];
        if (! isset($payload['time']) || ! \is_string($payload['time']) || $payload['time'] === '') {
            throw new ClientException('Invalid CloudEvent: missing string time.');
        }

        try {
            $time = new DateTimeImmutable($payload['time']);
        } catch (Exception $e) {
            throw new ClientException('Invalid CloudEvent: malformed time.', previous: $e);
        }

        $type = (string) ($payload['type'] ?? '');

        return new self(
            specVersion: (string) ($payload['specversion'] ?? ''),
            type: $type,
            source: (string) ($payload['source'] ?? ''),
            subject: (string) ($payload['subject'] ?? ''),
            id: (string) ($payload['id'] ?? ''),
            time: $time,
            dataContentType: (string) ($payload['datacontenttype'] ?? ''),
            data: $decode($type, \is_array($data) ? $data : []),
        );
    }
}
