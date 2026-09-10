<?php

declare(strict_types=1);

namespace OpenRuntimes\Orchestrator\Tests\Callback;

use OpenRuntimes\Orchestrator\Callback\Failure;
use OpenRuntimes\Orchestrator\Exception\ClientException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FailureTest extends TestCase
{
    public function test_reads_code_and_message(): void
    {
        $failure = Failure::fromData(['status' => 'failed', 'error' => ['code' => 'archive_unknown_format', 'message' => 'Unrecognized archive format for source.tar.gz']]);

        $this->assertInstanceOf(Failure::class, $failure);
        $this->assertSame('archive_unknown_format', $failure->code);
        $this->assertSame('Unrecognized archive format for source.tar.gz', $failure->message);
    }

    public function test_success_has_no_failure(): void
    {
        $this->assertNull(Failure::fromData(['status' => 'success']));
    }

    public function test_accepts_legacy_string_error(): void
    {
        $failure = Failure::fromData(['error' => 'job_oom']);

        $this->assertInstanceOf(Failure::class, $failure);
        $this->assertSame('job_oom', $failure->code);
        $this->assertSame('', $failure->message);
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function malformedErrors(): iterable
    {
        yield 'null' => [null];
        yield 'no code' => [['message' => 'no code']];
        yield 'non-string message' => [['code' => 'job_oom', 'message' => ['nested']]];
    }

    #[DataProvider('malformedErrors')]
    public function test_rejects_malformed_error(mixed $error): void
    {
        $this->expectException(ClientException::class);

        Failure::fromData(['error' => $error]);
    }
}
