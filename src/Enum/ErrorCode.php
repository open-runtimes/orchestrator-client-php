<?php

declare(strict_types=1);

namespace OpenRuntimes\Orchestrator\Enum;

/**
 * The stable half of a callback's error. See the orchestrator's callbacks
 * guide for what each one means.
 */
enum FailureCode: string
{
    // orchestrator.job.artifact
    case ArchiveEmpty = 'archive_empty';
    case ArchiveUnknownFormat = 'archive_unknown_format';
    case ArchiveCorrupt = 'archive_corrupt';
    case ArchivePathInvalid = 'archive_path_invalid';
    case ArchiveLayoutMismatch = 'archive_layout_mismatch';
    case ArchiveExtractionFailed = 'archive_extraction_failed';
    case ArchiveCreationFailed = 'archive_creation_failed';
    case ArchiveCompressionUnsupported = 'archive_compression_unsupported';
    case ArtifactNotFound = 'artifact_not_found';
    case ArtifactPermissionDenied = 'artifact_permission_denied';
    case ArtifactTimeout = 'artifact_timeout';
    case ArtifactCanceled = 'artifact_canceled';
    case ArtifactReadFailed = 'artifact_read_failed';
    case ArtifactWriteFailed = 'artifact_write_failed';
    case ArtifactStatFailed = 'artifact_stat_failed';
    case ArtifactListFailed = 'artifact_list_failed';
    case ArtifactJsonInvalid = 'artifact_json_invalid';
    case DownloadFailed = 'download_failed';
    case DownloadHttpError = 'download_http_error';
    case UploadFailed = 'upload_failed';
    case CloneFailed = 'clone_failed';
    case MountFailed = 'mount_failed';
    case ArtifactFailed = 'artifact_failed';

    // orchestrator.job.exit
    case JobExitNonzero = 'job_exit_nonzero';
    case JobFailed = 'job_failed';
    case JobOom = 'job_oom';

    // orchestrator.deployment.response
    case DeploymentNoCapacity = 'deployment_no_capacity';
    case DeploymentForwardFailed = 'deployment_forward_failed';
    case DeploymentResponseUnreadable = 'deployment_response_unreadable';
}
