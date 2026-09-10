<?php

declare(strict_types=1);

namespace OpenRuntimes\Orchestrator\Callback;

use OpenRuntimes\Orchestrator\Exception\ClientException;
use OpenRuntimes\Orchestrator\Model\Data;

/**
 * orchestrator.deployment.response — an async request completed. statusCode and
 * body are null when the request never reached a replica; $error then says
 * why. A body that is not valid UTF-8 arrives base64-encoded with bodyEncoding
 * "base64". requestHeaders is null when it was dropped for size.
 */
final readonly class DeploymentResponse implements Callback
{
    public function __construct(
        public string $deploymentId,
        public string $invocationId,
        public string $requestMethod,
        public string $requestPath,
        public bool $requestPathTruncated,
        /** @var array<string, list<string>>|null */
        public ?array $requestHeaders,
        public bool $requestHeadersTruncated,
        public ?float $durationSeconds,
        public ?int $statusCode,
        public ?string $body,
        public ?string $bodyEncoding,
        public bool $bodyTruncated,
        public ?Error $error,
    ) {}

    public static function fromArray(array $data): static
    {
        $headers = $data['requestHeaders'] ?? null;
        if ($headers !== null && ! \is_array($headers)) {
            throw new ClientException('Invalid deployment response: requestHeaders must be an object.');
        }

        /** @var array<string, list<string>>|null $headers */
        return new self(
            deploymentId: Data::string($data, 'deploymentId', 'deployment response'),
            invocationId: Data::string($data, 'invocationId', 'deployment response'),
            requestMethod: Data::string($data, 'requestMethod', 'deployment response'),
            requestPath: Data::string($data, 'requestPath', 'deployment response'),
            requestPathTruncated: Data::bool($data, 'requestPathTruncated', 'deployment response'),
            requestHeaders: $headers,
            requestHeadersTruncated: Data::bool($data, 'requestHeadersTruncated', 'deployment response'),
            durationSeconds: Data::optionalFloat($data, 'durationSeconds', 'deployment response'),
            statusCode: Data::optionalInt($data, 'statusCode', 'deployment response'),
            body: Data::optionalString($data, 'body', 'deployment response'),
            bodyEncoding: Data::optionalString($data, 'bodyEncoding', 'deployment response'),
            bodyTruncated: Data::bool($data, 'bodyTruncated', 'deployment response'),
            error: Error::fromData($data),
        );
    }
}
