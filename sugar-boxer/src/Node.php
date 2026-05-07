<?php

declare(strict_types=1);

namespace SugarCraft\Boxer;

/**
 * Immutable layout tree node.
 *
 * @property NodeKind              $kind         LEAF | HORIZONTAL | VERTICAL | NOBORDER
 * @property list<Node>            $children     Child nodes (empty for LEAF)
 * @property string                $content      Leaf string content
 * @property int                   $minWidth
 * @property int                   $maxWidth
 * @property int                   $minHeight
 * @property int                   $maxHeight
 * @property int                   $padding      Inner padding (cells)
 * @property bool                  $border       Whether to draw box border
 * @property int                   $spacing      Gap between children (cells)
 */
final class Node
{
    public const LEAF       = 'leaf';
    public const HORIZONTAL = 'horizontal';
    public const VERTICAL   = 'vertical';
    public const NOBORDER   = 'noborder';

    public readonly string $kind;
    public readonly array $children;
    public readonly string $content;
    public readonly int $minWidth;
    public readonly int $maxWidth;
    public readonly int $minHeight;
    public readonly int $maxHeight;
    public readonly int $padding;
    public readonly bool $border;
    public readonly int $spacing;

    private function __construct(
        string $kind,
        string $content = '',
        array $children = [],
        int $minWidth = 0,
        int $maxWidth = 0,
        int $minHeight = 0,
        int $maxHeight = 0,
        int $padding = 0,
        bool $border = true,
        int $spacing = 0,
    ) {
        $this->kind        = $kind;
        $this->content     = $content;
        $this->children    = $children;
        $this->minWidth    = $minWidth;
        $this->maxWidth    = $maxWidth;
        $this->minHeight   = $minHeight;
        $this->maxHeight   = $maxHeight;
        $this->padding     = $padding;
        $this->border      = $border;
        $this->spacing     = $spacing;
    }

    // -------------------------------------------------------------------------
    // Factory constructors
    // -------------------------------------------------------------------------

    public static function leaf(string $content = ''): self
    {
        return new self(self::LEAF, $content);
    }

    public static function horizontal(Node ...$children): self
    {
        return new self(self::HORIZONTAL, '', $children);
    }

    public static function vertical(Node ...$children): self
    {
        return new self(self::VERTICAL, '', $children);
    }

    /**
     * No-border wrapper — children rendered flat without vertical separators.
     */
    public static function noBorder(Node $child): self
    {
        return new self(self::NOBORDER, '', [$child]);
    }

    // -------------------------------------------------------------------------
    // With* builders
    // -------------------------------------------------------------------------

    public function withMinWidth(int $w): self
    {
        return $this->with(minWidth: $w);
    }

    public function withMaxWidth(int $w): self
    {
        return $this->with(maxWidth: $w);
    }

    public function withMinHeight(int $h): self
    {
        return $this->with(minHeight: $h);
    }

    public function withMaxHeight(int $h): self
    {
        return $this->with(maxHeight: $h);
    }

    public function withPadding(int $cells): self
    {
        return $this->with(padding: $cells);
    }

    public function withBorder(bool $show): self
    {
        return $this->with(border: $show);
    }

    public function withSpacing(int $cells): self
    {
        return $this->with(spacing: $cells);
    }

    public function withContent(string $content): self
    {
        return new self(
            self::LEAF,
            content: $content,
        );
    }

    // -------------------------------------------------------------------------
    // Dimension queries
    // -------------------------------------------------------------------------

    /** Total width including border and padding. */
    public function totalWidth(): int
    {
        if ($this->kind === self::LEAF) {
            $inner = $this->minWidth;
            if ($this->border) $inner += 2;
            if ($this->padding > 0) $inner += $this->padding * 2;
            return $inner;
        }

        $childWidths = \array_sum(\array_map(fn(Node $c) => $c->totalWidth(), $this->children));
        $gaps = (\count($this->children) - 1) * $this->spacing;

        // For HORIZONTAL, border is shared; for VERTICAL, each child may have border
        if ($this->kind === self::HORIZONTAL) {
            $extra = $this->border ? 2 : 0;
            return $childWidths + $gaps + $extra;
        }

        // VERTICAL: use max child width
        $maxChild = \count($this->children) > 0
            ? \max(...\array_map(fn(Node $c) => $c->totalWidth(), $this->children))
            : 0;
        $extra = $this->border ? 2 : 0;
        return $maxChild + $gaps + $extra;
    }

    /** Total height including border and padding. */
    public function totalHeight(): int
    {
        if ($this->kind === self::LEAF) {
            $inner = $this->minHeight;
            if ($this->border) $inner += 2;
            if ($this->padding > 0) $inner += $this->padding * 2;
            return $inner;
        }

        if ($this->kind === self::VERTICAL) {
            $childHeights = \array_sum(\array_map(fn(Node $c) => $c->totalHeight(), $this->children));
            $gaps = (\count($this->children) - 1) * $this->spacing;
            $extra = $this->border ? 2 : 0;
            return $childHeights + $gaps + $extra;
        }

        // HORIZONTAL: use max child height
        $maxChild = \count($this->children) > 0
            ? \max(...\array_map(fn(Node $c) => $c->totalHeight(), $this->children))
            : 0;
        $extra = $this->border ? 2 : 0;
        return $maxChild + $extra;
    }

    // -------------------------------------------------------------------------
    // Private
    // -------------------------------------------------------------------------

    private function with(
        int $minWidth = 0,
        int $maxWidth = 0,
        int $minHeight = 0,
        int $maxHeight = 0,
        int $padding = 0,
        bool $border = true,
        int $spacing = 0,
    ): self {
        return new self(
            $this->kind,
            $this->content,
            $this->children,
            $minWidth    ?: $this->minWidth,
            $maxWidth    ?: $this->maxWidth,
            $minHeight   ?: $this->minHeight,
            $maxHeight   ?: $this->maxHeight,
            $padding     ?: $this->padding,
            $border,
            $spacing     ?: $this->spacing,
        );
    }
}
