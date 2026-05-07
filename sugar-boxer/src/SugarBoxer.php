<?php

declare(strict_types=1);

namespace SugarCraft\Boxer;

/**
 * Box-drawing layout renderer.
 *
 * Builds a tree of {@see Node}s (leaf/horizontal/vertical/noborder) and
 * renders it as ANSI box-drawing characters within a fixed viewport.
 *
 * Port of treilik/bubbleboxer.
 *
 * @see https://github.com/treilik/bubbleboxer
 */
final class SugarBoxer
{
    // Box-drawing characters
    private const TL = '╭';  // top-left
    private const TR = '╮';  // top-right
    private const BL = '╰';  // bottom-left
    private const BR = '╯';  // bottom-right
    private const H  = '─';  // horizontal line
    private const V  = '│';  // vertical line
    private const CR = '┬';  // cross (top join)
    private const CL = '┤';  // cross (right join)
    private const CA = '├';  // cross (left join)
    private const CB = '┴';  // cross (bottom join)
    private const XX = '┼';  // center cross

    private const HL = '─';

    /**
     * Create a new SugarBoxer instance.
     */
    public static function new(): self
    {
        return new self();
    }

    // -------------------------------------------------------------------------
    // Node factory helpers (delegated to Node statics for ergonomics)
    // -------------------------------------------------------------------------

    public function leaf(string $content = ''): Node
    {
        return Node::leaf($content);
    }

    public function horizontal(Node ...$children): Node
    {
        return Node::horizontal(...$children);
    }

    public function vertical(Node ...$children): Node
    {
        return Node::vertical(...$children);
    }

    public function noBorder(Node $child): Node
    {
        return Node::noBorder($child);
    }

    // -------------------------------------------------------------------------
    // Rendering
    // -------------------------------------------------------------------------

    /**
     * Render a layout node tree into a string within the given viewport.
     *
     * @param Node $root   Root layout node
     * @param int  $width  Viewport width in cells
     * @param int  $height Viewport height in lines
     * @return string      Rendered layout with box-drawing characters
     */
    public function render(Node $root, int $width, int $height): string
    {
        $lines = \array_fill(0, $height, \str_repeat(' ', $width));
        $this->renderNode($root, 0, 0, $width, $height, $lines);
        return \implode("\n", $lines);
    }

    /**
     * Render a node at the given viewport region.
     *
     * @param Node    $node   Node to render
     * @param int     $x      Left offset
     * @param int     $y      Top offset
     * @param int     $w      Available width
     * @param int     $h      Available height
     * @param array   $lines  Mutable lines array (modified in-place)
     */
    private function renderNode(Node $node, int $x, int $y, int $w, int $h, array &$lines): void
    {
        if ($w <= 0 || $h <= 0) return;

        switch ($node->kind) {
            case Node::LEAF:
                $this->renderLeaf($node, $x, $y, $w, $h, $lines);
                break;
            case Node::HORIZONTAL:
                $this->renderHorizontal($node, $x, $y, $w, $h, $lines);
                break;
            case Node::VERTICAL:
                $this->renderVertical($node, $x, $y, $w, $h, $lines);
                break;
            case Node::NOBORDER:
                $this->renderNoBorder($node, $x, $y, $w, $h, $lines);
                break;
        }
    }

    private function renderLeaf(Node $node, int $x, int $y, int $w, int $h, array &$lines): void
    {
        $b = $node->border ? 1 : 0;
        $p = $node->padding;

        $cx = $x + $b;          // content left
        $cy = $y + $b;          // content top
        $cw = $w - $b * 2;      // content width
        $ch = $h - $b * 2;      // content height

        if ($cw <= 0 || $ch <= 0) return;

        $pcx = $cx + $p;        // padded content left
        $pcy = $cy + $p;        // padded content top
        $pcw = $cw - $p * 2;    // padded content width
        $pch = $ch - $p * 2;    // padded content height

        if ($pcw <= 0 || $pch <= 0) return;

        // Draw border
        if ($node->border) {
            $this->drawBorder($x, $y, $w, $h, $lines);
        }

        // Draw content with word wrap
        $this->renderContent($node->content, $pcx, $pcy, $pcw, $pch, $lines);
    }

    private function renderHorizontal(Node $node, int $x, int $y, int $w, int $h, array &$lines): void
    {
        $children = $node->children;
        $n = \count($children);
        if ($n === 0) return;

        $b  = $node->border ? 1 : 0;
        $sp = $node->spacing;

        $availableW = $w - $b * 2;
        $availableH = $h - $b * 2;

        if ($availableW <= 0 || $availableH <= 0) return;

        // Distribute width proportionally by minWidth weights
        $weights = \array_map(fn(Node $c) => $c->minWidth > 0 ? $c->minWidth : 1, $children);
        $totalWeight = \array_sum($weights);

        $offsets = $this->distribute($availableW, $weights, $totalWeight, $sp, $b);

        // Draw outer border first
        if ($node->border) {
            $this->drawBorder($x, $y, $w, $h, $lines);
        }

        // Render each child
        for ($i = 0; $i < $n; $i++) {
            $child = $children[$i];
            $ox = $x + $b + $offsets[$i];
            $ow = $i === $n - 1
                ? $w - $b - $offsets[$i] - $b
                : $offsets[$i + 1] - $offsets[$i] - $sp;

            $this->renderNode($child, $ox, $y + $b, $ow, $availableH, $lines);

            // Draw vertical separator between children (inside the outer border)
            if ($i < $n - 1 && $sp === 0) {
                $this->drawVLine($x + $offsets[$i + 1] - 1, $y + $b, $availableH, $lines);
            }
        }
    }

    private function renderVertical(Node $node, int $x, int $y, int $w, int $h, array &$lines): void
    {
        $children = $node->children;
        $n = \count($children);
        if ($n === 0) return;

        $b  = $node->border ? 1 : 0;
        $sp = $node->spacing;

        $availableW = $w - $b * 2;
        $availableH = $h - $b * 2;

        if ($availableW <= 0 || $availableH <= 0) return;

        $weights = \array_map(fn(Node $c) => $c->minHeight > 0 ? $c->minHeight : 1, $children);
        $totalWeight = \array_sum($weights);

        $offsets = $this->distribute($availableH, $weights, $totalWeight, $sp, $b);

        if ($node->border) {
            $this->drawBorder($x, $y, $w, $h, $lines);
        }

        for ($i = 0; $i < $n; $i++) {
            $child = $children[$i];
            $oy = $y + $b + $offsets[$i];
            $oh = $i === $n - 1
                ? $h - $b - $offsets[$i] - $b
                : $offsets[$i + 1] - $offsets[$i] - $sp;

            $this->renderNode($child, $x + $b, $oy, $availableW, $oh, $lines);

            if ($i < $n - 1 && $sp === 0) {
                $this->drawHLine($x + $b, $y + $offsets[$i + 1] - 1, $availableW, $lines);
            }
        }
    }

    private function renderNoBorder(Node $node, int $x, int $y, int $w, int $h, array &$lines): void
    {
        if ($node->children === []) return;
        $this->renderNode($node->children[0], $x, $y, $w, $h, $lines);
    }

    // -------------------------------------------------------------------------
    // Content rendering
    // -------------------------------------------------------------------------

    /**
     * Render text content within a content region with word-wrap.
     */
    private function renderContent(string $text, int $x, int $y, int $w, int $h, array &$lines): void
    {
        if ($w <= 0 || $h <= 0) return;

        $wrapped = $this->wordWrap($text, $w);
        $linesToRender = \array_slice($wrapped, 0, $h);

        foreach ($linesToRender as $lineIdx => $line) {
            $lineY = $y + $lineIdx;
            if ($lineY >= \count($lines)) break;

            // Write characters one by one to handle multibyte
            $chars = \preg_split('//u', $line, -1, \PREG_SPLIT_NO_EMPTY) ?: [];
            for ($i = 0; $i < $w; $i++) {
                $char = $chars[$i] ?? ' ';
                $this->setChar($x + $i, $lineY, $char, $lines);
            }
        }
    }

    /**
     * Word-wrap text to fit a given column width.
     *
     * @return list<string>
     */
    private function wordWrap(string $text, int $width): array
    {
        if ($width <= 0) return [''];

        $result = [];
        foreach (\explode("\n", $text) as $paragraphLine) {
            $words = \preg_split('/\s+/', $paragraphLine) ?: [];
            $current = '';

            foreach ($words as $word) {
                $test = $current === '' ? $word : $current . ' ' . $word;
                if ($this->strWidth($test) <= $width) {
                    $current = $test;
                } else {
                    if ($current !== '') {
                        $result[] = $current;
                    }
                    if ($this->strWidth($word) > $width) {
                        // Split oversized word
                        $result = \array_merge($result, $this->splitWord($word, $width));
                    } else {
                        $current = $word;
                    }
                }
            }

            if ($current !== '') {
                $result[] = $current;
            }
        }

        return $result ?: [''];
    }

    private function splitWord(string $word, int $width): array
    {
        $chunks = [];
        $len = \mb_strlen($word, 'UTF-8');
        for ($i = 0; $i < $len; $i += $width) {
            $chunks[] = \mb_substr($word, $i, $width, 'UTF-8');
        }
        return $chunks ?: [''];
    }

    // -------------------------------------------------------------------------
    // Border drawing
    // -------------------------------------------------------------------------

    private function drawBorder(int $x, int $y, int $w, int $h, array &$lines): void
    {
        if ($w < 2 || $h < 2) return;

        // Corners
        $this->setChar($x,             $y,             self::TL, $lines);
        $this->setChar($x + $w - 1,   $y,             self::TR, $lines);
        $this->setChar($x,             $y + $h - 1,   self::BL, $lines);
        $this->setChar($x + $w - 1,   $y + $h - 1,   self::BR, $lines);

        // Top/bottom edges
        for ($i = 1; $i < $w - 1; $i++) {
            $this->setChar($x + $i, $y,             self::H,  $lines);
            $this->setChar($x + $i, $y + $h - 1,   self::H,  $lines);
        }

        // Left/right edges
        for ($j = 1; $j < $h - 1; $j++) {
            $this->setChar($x,             $y + $j, self::V,  $lines);
            $this->setChar($x + $w - 1,   $y + $j, self::V,  $lines);
        }
    }

    private function drawVLine(int $x, int $y, int $h, array &$lines): void
    {
        for ($j = 0; $j < $h; $j++) {
            $this->setChar($x, $y + $j, self::V, $lines);
        }
    }

    private function drawHLine(int $x, int $y, int $w, array &$lines): void
    {
        for ($i = 0; $i < $w; $i++) {
            $this->setChar($x + $i, $y, self::H, $lines);
        }
    }

    // -------------------------------------------------------------------------
    // Cell-level operations
    // -------------------------------------------------------------------------

    /**
     * Set a single character in the lines buffer.
     */
    private function setChar(int $x, int $y, string $char, array &$lines): void
    {
        if ($y < 0 || $y >= \count($lines)) return;
        if ($x < 0) return;

        $line = $lines[$y];
        $lineLen = \strlen($line);

        if ($x >= $lineLen) {
            $line = \str_pad($line, $x + 1, ' ');
        }

        // Handle multibyte: replace character at position x
        if ($x < \strlen($line)) {
            $pre  = \substr($line, 0, $x);
            $post = \substr($line, $x + \strlen($char));
            $line = $pre . $char . $post;
        } else {
            $line .= $char;
        }

        $lines[$y] = $line;
    }

    // -------------------------------------------------------------------------
    // Width distribution
    // -------------------------------------------------------------------------

    /**
     * Distribute available space across children by weight.
     *
     * @param list<int> $weights
     * @return list<int> Starting offsets for each child
     */
    private function distribute(int $available, array $weights, int $totalWeight, int $spacing, int $borderPad): array
    {
        $n = \count($weights);
        $offsets = [0 => $borderPad];

        for ($i = 0; $i < $n - 1; $i++) {
            $share = (int) \round($weights[$i] / $totalWeight * ($available - $spacing * ($n - 1)));
            $share = \max($share, 1);
            $offsets[] = $offsets[$i] + $share + $spacing;
        }

        return $offsets;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function strWidth(string $s): int
    {
        return \strlen(\preg_replace('/\x1b\[[0-9;]*[a-zA-Z]/', '', $s) ?: '');
    }
}
