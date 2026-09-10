<?php

declare(strict_types=1);

namespace OpenRuntimes\Orchestrator\Tests\Callback;

use OpenRuntimes\Orchestrator\Callback\CloudEvent;
use OpenRuntimes\Orchestrator\Callback\Failure;
use OpenRuntimes\Orchestrator\Exception\ClientException;
use PHPUnit\Framework\TestCase;

final class CloudEventTest extends TestCase
{
    public function test_requires_time(): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('Invalid CloudEvent: missing string time.');

        CloudEvent::fromArray([]);
    }

    public function test_rejects_malformed_time(): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('Invalid CloudEvent: malformed time.');

        CloudEvent::fromArray(['time' => 'not-a-time']);
    }

    public function test_exposes_failure(): void
    {
        $event = CloudEvent::fromArray([
            'time' => '2026-01-15T10:30:00Z',
            'type' => 'orchestrator.job.artifact',
            'data' => ['status' => 'failed', 'error' => ['code' => 'archive_unknown_format', 'message' => 'Unrecognized archive format for source.tar.gz']],
        ]);

        $failure = $event->failure();
        $this->assertInstanceOf(Failure::class, $failure);
        $this->assertSame('archive_unknown_format', $failure->code);
        $this->assertSame('Unrecognized archive format for source.tar.gz', $failure->message);
    }

    public function test_success_has_no_failure(): void
    {
        $event = CloudEvent::fromArray(['time' => '2026-01-15T10:30:00Z', 'data' => ['status' => 'success']]);

        $this->assertNotInstanceOf(Failure::class, $event->failure());
    }

    public function test_accepts_legacy_string_error(): void
    {
        $event = CloudEvent::fromArray(['time' => '2026-01-15T10:30:00Z', 'data' => ['error' => 'job_oom']]);

        $failure = $event->failure();
        $this->assertInstanceOf(Failure::class, $failure);
        $this->assertSame('job_oom', $failure->code);
        $this->assertSame('', $failure->message);
    }

    public function test_rejects_error_without_code(): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('Invalid callback error: missing string code.');

        CloudEvent::fromArray(['time' => '2026-01-15T10:30:00Z', 'data' => ['error' => ['message' => 'no code']]])->failure();
    }
}
