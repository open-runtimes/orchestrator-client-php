<?php

declare(strict_types=1);

namespace OpenRuntimes\Orchestrator\Tests\Callback;

use OpenRuntimes\Orchestrator\Callback\Failure;
use OpenRuntimes\Orchestrator\Enum\FailureCode;
use OpenRuntimes\Orchestrator\Exception\ClientException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FailureTest extends TestCase
{
    public function test_reads_code_and_message(): void
    {
        $failure = Failure::fromData(['status' => 'failed', 'error' => ['code' => 'archive_unknown_format', 'message' => 'Unrecognized archive format for source.tar.gz']]);

        $this->assertInstanceOf(Failure::class, $failure);
        $this->assertSame(FailureCode::ArchiveUnknownFormat, $failure->code);
        $this->assertSame('Unrecognized archive format for source.tar.gz', $failure->message);
    }

    public function test_success_has_no_failure(): void
    {
        $this->assertNull(Failure::fromData(['status' => 'success']));
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function malformedErrors(): iterable
    {
        yield 'null' => [null];
        yield 'bare string' => ['job_oom'];
        yield 'no code' => [['message' => 'no code']];
        yield 'unknown code' => [['code' => 'job_teleported', 'message' => 'Job teleported']];
        yield 'no message' => [['code' => 'job_oom']];
    }

    #[DataProvider('malformedErrors')]
    public function test_rejects_malformed_error(mixed $error): void
    {
        $this->expectException(ClientException::class);

        Failure::fromData(['error' => $error]);
    }
}
