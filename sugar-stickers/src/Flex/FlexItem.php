<?php

declare(strict_types=1);

namespace SugarCraft\Stickers\Flex;

/**
 * A single item inside a FlexBox.
 *
 * Holds content + layout constraints (ratio, basis, grow/shrink factors).
 */
final class FlexItem
{
    public readonly string $content;

    /** Relative size ratio — items with higher ratios take more space. */
    public readonly int $ratio;

    /** Fixed basis (0 = fill available). */
    public readonly int $basis;

    /** Grow factor (flex-grow). */
    public readonly int $grow;

    /** Shrink factor (flex-shrink). */
    public readonly int $shrink;

    /** Style applied to this item (ANSI string). */
    public readonly string $style;

    private function __construct(
        string $content,
        int $ratio = 1,
        int $basis = 0,
        int $grow = 1,
        int $shrink = 1,
        string $style = '',
    ) {
        $this->content = $content;
        $this->ratio   = $ratio;
        $this->basis   = $basis;
        $this->grow    = $grow;
        $this->shrink  = $shrink;
        $this->style   = $style;
    }

    public static function new(string $content = ''): self
    {
        return new self($content);
    }

    public function withContent(string $content): self
    {
        return new self($content, $this->ratio, $this->basis, $this->grow, $this->shrink, $this->style);
    }

    public function withRatio(int $ratio): self
    {
        return new self($this->content, $ratio, $this->basis, $this->grow, $this->shrink, $this->style);
    }

    public function withBasis(int $basis): self
    {
        return new self($this->content, $this->ratio, $basis, $this->grow, $this->shrink, $this->style);
    }

    public function withGrow(int $grow): self
    {
        return new self($this->content, $this->ratio, $this->basis, $grow, $this->shrink, $this->style);
    }

    public function withShrink(int $shrink): self
    {
        return new self($this->content, $this->ratio, $this->basis, $this->grow, $shrink, $this->style);
    }

    public function withStyle(string $ansiStyle): self
    {
        return new self($this->content, $this->ratio, $this->basis, $this->grow, $this->shrink, $ansiStyle);
    }

    public function withWidth(int $width): self
    {
        return $this->withBasis($width);
    }
}
