<?php

declare(strict_types=1);

namespace OpenRuntimes\Orchestrator\Callback;

use OpenRuntimes\Orchestrator\Model\Data;

/**
 * orchestrator.job.log — a batch of stdout or stderr lines.
 */
final readonly class JobLog implements Callback
{
    public function __construct(
        public string $jobId,
        /** @var list<string> */
        public array $lines,
        public string $stream,
        /** @var array<string, string> */
        public array $meta,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            jobId: Data::string($data, 'jobId', 'job log'),
            lines: Data::strings($data, 'lines', 'job log'),
            stream: Data::string($data, 'stream', 'job log'),
            meta: Data::stringMap($data, 'meta', 'job log'),
        );
    }
}
