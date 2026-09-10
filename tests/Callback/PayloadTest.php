<?php

declare(strict_types=1);

namespace OpenRuntimes\Orchestrator\Tests\Callback;

use OpenRuntimes\Orchestrator\Callback\CloudEvent;
use OpenRuntimes\Orchestrator\Callback\Failure;
use OpenRuntimes\Orchestrator\Callback\Payload;
use OpenRuntimes\Orchestrator\Callback\Payload\DeploymentResponse;
use OpenRuntimes\Orchestrator\Callback\Payload\JobArtifact;
use OpenRuntimes\Orchestrator\Callback\Payload\JobComplete;
use OpenRuntimes\Orchestrator\Callback\Payload\JobExit;
use OpenRuntimes\Orchestrator\Callback\Payload\JobLog;
use OpenRuntimes\Orchestrator\Callback\Payload\JobStart;
use OpenRuntimes\Orchestrator\Enum\CallbackEvent;
use OpenRuntimes\Orchestrator\Enum\FailureCode;
use OpenRuntimes\Orchestrator\Exception\ClientException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ValueError;

final class PayloadTest extends TestCase
{
    /**
     * @param  array<string, mixed>  $data
     * @return CloudEvent<Payload>
     */
    private function decode(string $type, array $data): CloudEvent
    {
        return CloudEvent::decode(
            ['specversion' => '1.0', 'type' => $type, 'source' => 'orchestrator/service', 'subject' => 'job-1', 'id' => 'job-1-1', 'time' => '2026-01-15T10:30:00Z', 'data' => $data],
            static fn (string $type, array $data): Payload => CallbackEvent::from($type)->decode($data),
        );
    }

    /**
     * @return iterable<string, array{string, array<string, mixed>, class-string<Payload>}>
     */
    public static function events(): iterable
    {
        yield 'start' => ['orchestrator.job.start', ['jobId' => 'job-1', 'meta' => []], JobStart::class];
        yield 'log' => ['orchestrator.job.log', ['jobId' => 'job-1', 'lines' => ['a', 'b'], 'stream' => 'stdout', 'meta' => []], JobLog::class];
        yield 'artifact' => ['orchestrator.job.artifact', ['jobId' => 'job-1', 'artifactId' => 'source', 'artifactType' => 'download', 'status' => 'success', 'durationSeconds' => 0.4, 'meta' => []], JobArtifact::class];
        yield 'exit' => ['orchestrator.job.exit', ['jobId' => 'job-1', 'exitCode' => 0, 'image' => 'alpine', 'durationSeconds' => 3, 'meta' => []], JobExit::class];
        yield 'complete' => ['orchestrator.job.complete', ['jobId' => 'job-1', 'meta' => []], JobComplete::class];
        yield 'response' => ['orchestrator.deployment.response', ['deploymentId' => 'dep', 'invocationId' => 'inv', 'requestMethod' => 'POST', 'requestPath' => '/', 'statusCode' => 200, 'body' => 'ok', 'bodyTruncated' => false], DeploymentResponse::class];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  class-string<Payload>  $payload
     */
    #[DataProvider('events')]
    public function test_decodes_each_event_to_its_payload(string $type, array $data, string $payload): void
    {
        $event = $this->decode($type, $data);

        $this->assertSame($type, $event->type);
        $this->assertInstanceOf($payload, $event->data);
    }

    public function test_artifact_failure(): void
    {
        $event = $this->decode('orchestrator.job.artifact', [
            'jobId' => 'job-1', 'artifactId' => 'extract', 'artifactType' => 'unarchive', 'status' => 'failed', 'durationSeconds' => 0.01,
            'error' => ['code' => 'archive_unknown_format', 'message' => 'Unrecognized archive format for source.tar.gz'],
            'meta' => ['deploymentId' => 'dep'],
        ]);

        $artifact = $event->data;
        $this->assertInstanceOf(JobArtifact::class, $artifact);
        $this->assertSame('failed', $artifact->status);
        $this->assertInstanceOf(Failure::class, $artifact->failure);
        $this->assertSame(FailureCode::ArchiveUnknownFormat, $artifact->failure->code);
        $this->assertSame('Unrecognized archive format for source.tar.gz', $artifact->failure->message);
        $this->assertNull($artifact->format);
        $this->assertSame(['deploymentId' => 'dep'], $artifact->meta);
    }

    public function test_exit_before_worker_ran(): void
    {
        $event = $this->decode('orchestrator.job.exit', [
            'jobId' => 'job-1', 'exitCode' => -1, 'reason' => 'init container failed', 'image' => 'alpine', 'durationSeconds' => 0,
            'error' => ['code' => 'job_failed', 'message' => 'Job failed before it could start'], 'meta' => [],
        ]);

        $exit = $event->data;
        $this->assertInstanceOf(JobExit::class, $exit);
        $this->assertSame(-1, $exit->exitCode);
        $this->assertSame('init container failed', $exit->reason);
        $this->assertSame(FailureCode::JobFailed, $exit->failure?->code);
    }

    public function test_response_that_never_reached_a_replica(): void
    {
        $event = $this->decode('orchestrator.deployment.response', [
            'deploymentId' => 'dep', 'invocationId' => 'inv', 'requestMethod' => 'GET', 'requestPath' => '/health',
            'requestHeaders' => ['X-Trace' => ['abc']],
            'error' => ['code' => 'deployment_no_capacity', 'message' => 'The deployment had no capacity ready in time to serve the request'],
        ]);

        $response = $event->data;
        $this->assertInstanceOf(DeploymentResponse::class, $response);
        $this->assertNull($response->statusCode);
        $this->assertNull($response->body);
        $this->assertSame(['X-Trace' => ['abc']], $response->requestHeaders);
        $this->assertSame(FailureCode::DeploymentNoCapacity, $response->failure?->code);
    }

    public function test_unknown_event_type_is_rejected(): void
    {
        $this->expectException(ValueError::class);

        $this->decode('orchestrator.job.teleport', []);
    }

    public function test_missing_field_is_rejected(): void
    {
        $this->expectException(ClientException::class);

        $this->decode('orchestrator.job.exit', ['jobId' => 'job-1']);
    }
}
