<?php

declare(strict_types=1);

namespace CandyCore\Core\Msg;

use CandyCore\Core\Msg;
use CandyCore\Core\Util\ColorProfile;

/**
 * Emitted once at startup with the auto-detected colour profile of
 * the active terminal (and again whenever the active profile changes,
 * if a future query reports a downgrade). Models can react by picking
 * different palettes per tier.
 */
final class ColorProfileMsg implements Msg
{
    public function __construct(
        public readonly ColorProfile $profile,
    ) {}
}
