<?php

declare(strict_types=1);

namespace CandyCore\Boxer\Tests;

use CandyCore\Boxer\{Node, SugarBoxer};
use PHPUnit\Framework\TestCase;

final class SugarBoxerTest extends TestCase
{
    private SugarBoxer $boxer;

    protected function setUp(): void
    {
        $this->boxer = SugarBoxer::new();
    }

    public function testNewBoxer(): void
    {
        $b = SugarBoxer::new();
        $this->assertInstanceOf(SugarBoxer::class, $b);
    }

    public function testLeafNode(): void
    {
        $n = Node::leaf('hello');
        $this->assertSame(Node::LEAF, $n->kind);
        $this->assertSame('hello', $n->content);
    }

    public function testHorizontalNode(): void
    {
        $n = Node::horizontal(Node::leaf('a'), Node::leaf('b'));
        $this->assertSame(Node::HORIZONTAL, $n->kind);
        $this->assertCount(2, $n->children);
    }

    public function testVerticalNode(): void
    {
        $n = Node::vertical(Node::leaf('top'), Node::leaf('bottom'));
        $this->assertSame(Node::VERTICAL, $n->kind);
        $this->assertCount(2, $n->children);
    }

    public function testNodeWithPadding(): void
    {
        $n = Node::leaf('x')->withPadding(2);
        $this->assertSame(2, $n->padding);
    }

    public function testNodeWithBorder(): void
    {
        $n = Node::leaf('x')->withBorder(false);
        $this->assertFalse($n->border);
    }

    public function testNodeWithSpacing(): void
    {
        $n = Node::horizontal(Node::leaf('a'), Node::leaf('b'))->withSpacing(2);
        $this->assertSame(2, $n->spacing);
    }

    public function testNodeTotalWidth(): void
    {
        $leaf = Node::leaf('hello')->withMinWidth(5);
        $h = Node::horizontal($leaf, Node::leaf('world')->withMinWidth(5))->withBorder(true);
        $this->assertGreaterThan(0, $h->totalWidth());
    }

    public function testNodeTotalHeight(): void
    {
        $v = Node::vertical(
            Node::leaf('a')->withMinHeight(1),
            Node::leaf('b')->withMinHeight(1),
        )->withBorder(true);

        $this->assertGreaterThan(0, $v->totalHeight());
    }

    public function testRenderEmptyLayout(): void
    {
        $layout = Node::leaf('');
        $result = $this->boxer->render($layout, 10, 5);
        $this->assertIsString($result);
    }

    public function testRenderLeafWithBorder(): void
    {
        $layout = Node::leaf('content')->withBorder(true)->withMinWidth(10)->withMinHeight(3);
        $result = $this->boxer->render($layout, 14, 5);

        // Should contain box-drawing chars
        $this->assertStringContainsString('╭', $result);
        $this->assertStringContainsString('╮', $result);
        $this->assertStringContainsString('╰', $result);
        $this->assertStringContainsString('╯', $result);
        $this->assertStringContainsString('content', $result);
    }

    public function testRenderLeafNoBorder(): void
    {
        $layout = Node::leaf('plain')->withBorder(false);
        $result = $this->boxer->render($layout, 10, 3);

        $this->assertStringNotContainsString('╭', $result);
        $this->assertStringContainsString('plain', $result);
    }

    public function testRenderHorizontalTwoPanels(): void
    {
        $layout = Node::horizontal(
            Node::leaf('LEFT')->withMinWidth(5),
            Node::leaf('RIGHT')->withMinWidth(5),
        )->withBorder(true);

        $result = $this->boxer->render($layout, 30, 5);

        $this->assertStringContainsString('LEFT',  $result);
        $this->assertStringContainsString('RIGHT', $result);
        $this->assertStringContainsString('│',     $result); // vertical separator
    }

    public function testRenderVerticalTwoPanels(): void
    {
        $layout = Node::vertical(
            Node::leaf('TOP')->withMinHeight(2),
            Node::leaf('BOTTOM')->withMinHeight(2),
        )->withBorder(true);

        $result = $this->boxer->render($layout, 20, 10);

        $this->assertStringContainsString('TOP',    $result);
        $this->assertStringContainsString('BOTTOM', $result);
        $this->assertStringContainsString('─',      $result); // horizontal separator
    }

    public function testRenderNestedLayout(): void
    {
        $layout = Node::vertical(
            Node::horizontal(
                Node::leaf('A')->withMinWidth(3),
                Node::leaf('B')->withMinWidth(3),
            )->withMinHeight(3),
            Node::leaf('C')->withMinHeight(2),
        )->withBorder(true);

        $result = $this->boxer->render($layout, 20, 10);

        $this->assertStringContainsString('A', $result);
        $this->assertStringContainsString('B', $result);
        $this->assertStringContainsString('C', $result);
    }

    public function testRenderNoBorder(): void
    {
        $layout = Node::noBorder(Node::leaf('nested'));
        $result = $this->boxer->render($layout, 10, 3);

        $this->assertStringContainsString('nested', $result);
    }

    public function testLeafWithPadding(): void
    {
        $layout = Node::leaf('padded')->withPadding(3)->withBorder(true)->withMinWidth(10);
        $result = $this->boxer->render($layout, 20, 5);

        $this->assertStringContainsString('padded', $result);
    }

    public function testRenderMultipleLines(): void
    {
        $multiline = "line1\nline2\nline3";
        $layout = Node::leaf($multiline)->withBorder(true)->withMinWidth(10)->withMinHeight(5);
        $result = $this->boxer->render($layout, 20, 8);

        $this->assertStringContainsString('line1', $result);
        $this->assertStringContainsString('line2', $result);
        $this->assertStringContainsString('line3', $result);
    }

    public function testWithContent(): void
    {
        $n = Node::leaf('')->withContent('updated');
        $this->assertSame('updated', $n->content);
    }

    public function testWithDimensionConstraints(): void
    {
        $n = Node::leaf('x')
            ->withMinWidth(10)
            ->withMaxWidth(50)
            ->withMinHeight(5)
            ->withMaxHeight(20);

        $this->assertSame(10, $n->minWidth);
        $this->assertSame(50, $n->maxWidth);
        $this->assertSame(5,  $n->minHeight);
        $this->assertSame(20, $n->maxHeight);
    }
}
