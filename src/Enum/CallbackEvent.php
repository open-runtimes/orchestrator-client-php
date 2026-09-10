<?php

declare(strict_types=1);

namespace OpenRuntimes\Orchestrator\Enum;

use OpenRuntimes\Orchestrator\Callback\Payload;
use OpenRuntimes\Orchestrator\Callback\Payload\DeploymentResponse;
use OpenRuntimes\Orchestrator\Callback\Payload\JobArtifact;
use OpenRuntimes\Orchestrator\Callback\Payload\JobComplete;
use OpenRuntimes\Orchestrator\Callback\Payload\JobExit;
use OpenRuntimes\Orchestrator\Callback\Payload\JobLog;
use OpenRuntimes\Orchestrator\Callback\Payload\JobStart;

enum CallbackEvent: string
{
    case Start = 'orchestrator.job.start';
    case Artifact = 'orchestrator.job.artifact';
    case Log = 'orchestrator.job.log';
    case Exit = 'orchestrator.job.exit';
    case Complete = 'orchestrator.job.complete';
    case DeploymentResponse = 'orchestrator.deployment.response';

    /**
     * The typed payload of an event of this kind. Pass to CloudEvent::decode():
     *
     *     CloudEvent::decode($raw, fn (string $type, array $data) => CallbackEvent::from($type)->decode($data))
     *
     * @param  array<string, mixed>  $data
     */
    public function decode(array $data): Payload
    {
        return match ($this) {
            self::Start => JobStart::fromArray($data),
            self::Artifact => JobArtifact::fromArray($data),
            self::Log => JobLog::fromArray($data),
            self::Exit => JobExit::fromArray($data),
            self::Complete => JobComplete::fromArray($data),
            self::DeploymentResponse => DeploymentResponse::fromArray($data),
        };
    }
}
