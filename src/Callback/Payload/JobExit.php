<?php

declare(strict_types=1);

namespace OpenRuntimes\Orchestrator\Callback\Payload;

use OpenRuntimes\Orchestrator\Callback\Failure;
use OpenRuntimes\Orchestrator\Callback\Payload;
use OpenRuntimes\Orchestrator\Model\Data;

/**
 * orchestrator.job.exit — the worker exited. exitCode is -1 when the job failed
 * before the worker could run; reason is the backend's own detail ("oom") and
 * null when it has nothing to add beyond the code.
 */
final readonly class JobExit implements Payload
{
    public function __construct(
        public string $jobId,
        public int $exitCode,
        public ?string $reason,
        public string $image,
        public ?float $durationSeconds,
        public ?Failure $failure,
        /** @var array<string, string> */
        public array $meta,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            jobId: Data::string($data, 'jobId', 'job exit'),
            exitCode: Data::int($data, 'exitCode', 'job exit'),
            reason: Data::optionalString($data, 'reason', 'job exit'),
            image: Data::string($data, 'image', 'job exit'),
            durationSeconds: Data::optionalFloat($data, 'durationSeconds', 'job exit'),
            failure: Failure::fromData($data),
            meta: Data::stringMap($data, 'meta', 'job exit'),
        );
    }
}
