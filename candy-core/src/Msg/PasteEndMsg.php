<?php

declare(strict_types=1);

namespace CandyCore\Core\Msg;

use CandyCore\Core\Msg;

/**
 * Marks the end of a bracketed-paste region. Emitted *just before*
 * the {@see PasteMsg} that carries the full collected content, so
 * models that flipped state on {@see PasteStartMsg} can settle back
 * before they see the data.
 */
final class PasteEndMsg implements Msg
{
}
