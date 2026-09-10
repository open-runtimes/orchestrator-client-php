<?php

declare(strict_types=1);

namespace OpenRuntimes\Orchestrator\Callback;

use OpenRuntimes\Orchestrator\Model\Data;

/**
 * orchestrator.job.start — the worker container started.
 */
final readonly class JobStart implements Callback
{
    public function __construct(
        public string $jobId,
        /** @var array<string, string> */
        public array $meta,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            jobId: Data::string($data, 'jobId', 'job start'),
            meta: Data::stringMap($data, 'meta', 'job start'),
        );
    }
}
