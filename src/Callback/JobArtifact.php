<?php

declare(strict_types=1);

namespace OpenRuntimes\Orchestrator\Callback;

use OpenRuntimes\Orchestrator\Model\Data;

/**
 * orchestrator.job.artifact — one artifact finished. On failure, $error says
 * why; format and compression are what the artifact was sniffed to be, null
 * when it was never read far enough to tell.
 */
final readonly class JobArtifact implements Callback
{
    public function __construct(
        public string $jobId,
        public string $artifactId,
        public string $artifactType,
        public string $status,
        public mixed $content,
        public ?float $durationSeconds,
        public ?string $format,
        public ?string $compression,
        public ?Error $error,
        /** @var array<string, string> */
        public array $meta,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            jobId: Data::string($data, 'jobId', 'job artifact'),
            artifactId: Data::string($data, 'artifactId', 'job artifact'),
            artifactType: Data::string($data, 'artifactType', 'job artifact'),
            status: Data::string($data, 'status', 'job artifact'),
            content: $data['content'] ?? null,
            durationSeconds: Data::optionalFloat($data, 'durationSeconds', 'job artifact'),
            format: Data::optionalString($data, 'format', 'job artifact'),
            compression: Data::optionalString($data, 'compression', 'job artifact'),
            error: Error::fromData($data),
            meta: Data::stringMap($data, 'meta', 'job artifact'),
        );
    }
}
