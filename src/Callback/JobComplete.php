<?php

declare(strict_types=1);

namespace OpenRuntimes\Orchestrator\Callback\Payload;

use OpenRuntimes\Orchestrator\Callback\Payload;
use OpenRuntimes\Orchestrator\Model\Data;

/**
 * orchestrator.job.complete — every post-job artifact has been processed,
 * successfully or not. No more events follow for this job.
 */
final readonly class JobComplete implements Payload
{
    public function __construct(
        public string $jobId,
        /** @var array<string, string> */
        public array $meta,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            jobId: Data::string($data, 'jobId', 'job complete'),
            meta: Data::stringMap($data, 'meta', 'job complete'),
        );
    }
}
