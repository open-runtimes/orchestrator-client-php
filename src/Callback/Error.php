<?php

declare(strict_types=1);

namespace OpenRuntimes\Orchestrator\Callback;

use OpenRuntimes\Orchestrator\Enum\ErrorCode;
use OpenRuntimes\Orchestrator\Exception\ClientException;
use OpenRuntimes\Orchestrator\Model\Data;

/**
 * The error carried by a failed callback: a stable code to branch on, and a
 * sentence about this occurrence to show — never to parse.
 */
final readonly class Error
{
    public function __construct(
        public ErrorCode $code,
        public string $message,
    ) {}

    /**
     * The error a callback's data reports, or null when it reports success.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromData(array $data): ?self
    {
        if (! \array_key_exists('error', $data)) {
            return null;
        }
        if (! \is_array($data['error'])) {
            throw new ClientException('Invalid callback error: must be an object.');
        }

        /** @var ErrorCode $code */
        $code = Data::enum($data['error'], 'code', ErrorCode::class, 'callback error');

        return new self($code, Data::string($data['error'], 'message', 'callback error'));
    }
}
