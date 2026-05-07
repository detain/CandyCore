<?php

declare(strict_types=1);

namespace SugarCraft\Tetris;

use SugarCraft\Core\Msg;

/**
 * Dispatched once per gravity tick — a pure marker carrying no
 * payload. The {@see Game}'s tick handler steps the active piece
 * down one row (or locks it + spawns the next one if it can't
 * move further) and schedules the following tick.
 */
final class GravityMsg implements Msg
{
}
