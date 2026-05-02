<?php

declare(strict_types=1);

namespace CandyCore\Freeze;

/**
 * One styled run produced by {@see AnsiParser::parse()}. Holds the
 * literal text plus the foreground colour and attribute flags that
 * were active when those bytes were emitted.
 *
 * Background colours are not tracked — the SVG renderer paints the
 * frame background uniformly. Add it later if a use case appears.
 */
final class Segment
{
    public function __construct(
        public readonly string $text,
        public readonly ?string $fg,
        public readonly bool $bold,
        public readonly bool $italic,
        public readonly bool $underline,
    ) {}
}
