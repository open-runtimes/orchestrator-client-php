<?php

declare(strict_types=1);

namespace OpenRuntimes\Orchestrator\Callback;

/**
 * The data of one callback, typed by its event. Decode a raw envelope into
 * one with CallbackEvent::decode() — see CloudEvent::decode().
 */
interface Callback
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static;
}
