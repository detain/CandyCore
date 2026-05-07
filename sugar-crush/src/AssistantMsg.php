<?php

declare(strict_types=1);

namespace SugarCraft\Crush;

use SugarCraft\Core\Msg;

/**
 * Internal Msg dispatched once a backend completion arrives.
 * Carried by the Cmd that {@see Chat} schedules when the user
 * submits a turn — the Cmd does the (possibly slow) backend
 * call off the main fiber and dispatches this Msg back into
 * `update()` with the assistant's reply.
 */
final class AssistantMsg implements Msg
{
    public function __construct(public readonly Message $message)
    {}
}
