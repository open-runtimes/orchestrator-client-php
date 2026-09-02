<?php

declare(strict_types=1);

namespace OpenRuntimes\Orchestrator\Tests;

use OpenRuntimes\Orchestrator\Enum\RuntimeClass;
use OpenRuntimes\Orchestrator\Enum\SandboxState;
use OpenRuntimes\Orchestrator\Model\Artifact\DownloadArtifact;
use OpenRuntimes\Orchestrator\Model\Artifact\UnarchiveArtifact;
use OpenRuntimes\Orchestrator\Sandboxes;
use PHPUnit\Framework\TestCase;
use Utopia\Psr7\Response;
use Utopia\Psr7\Stream;

final class SandboxesTest extends TestCase
{
    public function test_create_serializes_the_complete_shape_and_hydrates_urls(): void
    {
        $http = new Client([new Response(201, body: new Stream(
            '{"id":"sbx-3f9c1a02","status":"ready","url":"http://s-abc.sandboxes.test",'
            .'"urls":{"3000":"http://s-abc.sandboxes.test","5173":"http://s-abc-5173.sandboxes.test"}}'
        ))]);

        $sandbox = new Sandboxes($http)->create(
            image: 'python:3.12-slim',
            port: 3000,
            ports: [5173],
            artifacts: [
                new DownloadArtifact('code', 'https://acme.test/app.tar.gz', 'app.tar.gz'),
                new UnarchiveArtifact('unpack', 'app.tar.gz', '.', depends: 'code'),
            ],
            timeoutSeconds: 0,
            idleTimeoutSeconds: 900,
            terminationGracePeriodSeconds: 60,
        );

        $this->assertSame('sbx-3f9c1a02', $sandbox->id);
        $this->assertSame(SandboxState::Ready, $sandbox->status);
        $this->assertSame('http://s-abc.sandboxes.test', $sandbox->url);
        $this->assertSame([
            '3000' => 'http://s-abc.sandboxes.test',
            '5173' => 'http://s-abc-5173.sandboxes.test',
        ], $sandbox->urls);
        $this->assertNull($sandbox->error);

        $request = $http->requests[0];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/v1/sandbox', (string) $request->getUri());
        $this->assertJsonStringEqualsJsonString(
            '{"image":"python:3.12-slim","port":3000,"ports":[5173],"artifacts":['
            .'{"id":"code","type":"download","in":"https://acme.test/app.tar.gz","out":"app.tar.gz"},'
            .'{"id":"unpack","type":"unarchive","depends":"code","in":"app.tar.gz","out":"."}'
            .'],"timeoutSeconds":0,"idleTimeoutSeconds":900,"terminationGracePeriodSeconds":60}',
            (string) $request->getBody(),
        );
    }

    public function test_create_sizes_the_pod(): void
    {
        $http = new Client([new Response(201, body: new Stream('{"id":"sbx-1","status":"ready","url":"http://s-abc.sandboxes.test"}'))]);

        new Sandboxes($http)->create(
            image: 'python:3.12-slim',
            port: 3000,
            cpu: 2.0,
            memory: 2048,
            runtimeClass: RuntimeClass::Gvisor,
        );

        $this->assertJsonStringEqualsJsonString(
            '{"image":"python:3.12-slim","port":3000,"cpu":2,"memory":2048,"runtimeClass":"gvisor"}',
            (string) $http->requests[0]->getBody(),
        );
    }

    public function test_a_failed_sandbox_is_a_status_not_an_error(): void
    {
        $http = new Client([new Response(201, body: new Stream(
            '{"id":"sbx-1","status":"failed","error":"artifact code: 404"}'
        ))]);

        $sandbox = new Sandboxes($http)->create(image: 'python:3.12-slim', port: 3000);

        $this->assertSame(SandboxState::Failed, $sandbox->status);
        $this->assertSame('artifact code: 404', $sandbox->error);
        $this->assertNull($sandbox->url);
    }

    public function test_a_read_reports_the_shape_the_sandbox_runs_in(): void
    {
        $http = new Client([
            new Response(200, body: new Stream(
                '{"id":"sbx-1","status":"ready","image":"python:3.12-slim","cpu":2,"memory":2048}'
            )),
            new Response(200, body: new Stream('{"id":"old-1","status":"ready"}')),
        ]);
        $sandboxes = new Sandboxes($http);

        $sandbox = $sandboxes->get('sbx-1');
        $this->assertSame('python:3.12-slim', $sandbox->image);
        $this->assertEqualsWithDelta(2.0, $sandbox->cpu, PHP_FLOAT_EPSILON);
        $this->assertSame(2048, $sandbox->memory);

        // A sandbox created before the orchestrator recorded a shape reports none.
        $legacy = $sandboxes->get('old-1');
        $this->assertNull($legacy->image);
        $this->assertNull($legacy->cpu);
        $this->assertNull($legacy->memory);
    }

    public function test_get_list_and_delete(): void
    {
        $http = new Client([
            new Response(200, body: new Stream('{"id":"sbx-1","status":"creating"}')),
            new Response(200, body: new Stream('{"sandboxes":[{"id":"sbx-1","status":"ready","url":"http://s-abc.sandboxes.test"}]}')),
            new Response(204),
        ]);
        $sandboxes = new Sandboxes($http);

        $this->assertSame(SandboxState::Creating, $sandboxes->get('sbx-1')->status);
        $this->assertCount(1, $sandboxes->list()->sandboxes);
        $sandboxes->delete('sbx-1');

        $this->assertSame('/v1/sandbox/sbx-1', (string) $http->requests[0]->getUri());
        $this->assertSame('/v1/sandbox', (string) $http->requests[1]->getUri());
        $this->assertSame('DELETE', $http->requests[2]->getMethod());
    }
}
