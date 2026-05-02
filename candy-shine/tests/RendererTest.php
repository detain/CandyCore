<?php

declare(strict_types=1);

namespace CandyCore\Shine\Tests;

use CandyCore\Shine\Renderer;
use CandyCore\Shine\Theme;
use PHPUnit\Framework\TestCase;

final class RendererTest extends TestCase
{
    private function plain(): Renderer
    {
        return new Renderer(Theme::plain());
    }

    public function testHeading1Plain(): void
    {
        $this->assertSame('# Hello', $this->plain()->render("# Hello"));
    }

    public function testHeading6Plain(): void
    {
        $this->assertSame('###### deep', $this->plain()->render("###### deep"));
    }

    public function testParagraphPlain(): void
    {
        $this->assertSame('hello world', $this->plain()->render('hello world'));
    }

    public function testStrongAndEmphasisPlain(): void
    {
        $this->assertSame(
            'a bold and italic text',
            $this->plain()->render('a **bold** and *italic* text'),
        );
    }

    public function testInlineCodePlain(): void
    {
        $this->assertSame(
            'use foo() to call',
            $this->plain()->render('use `foo()` to call'),
        );
    }

    public function testFencedCodeBlockPlain(): void
    {
        $md  = "```\nphp -v\nphp -m\n```";
        $out = $this->plain()->render($md);
        $this->assertStringContainsString('php -v', $out);
        $this->assertStringContainsString('php -m', $out);
    }

    public function testBulletedListPlain(): void
    {
        $md = "- one\n- two\n- three";
        $expected = "• one\n• two\n• three";
        $this->assertSame($expected, $this->plain()->render($md));
    }

    public function testOrderedListPlain(): void
    {
        $md = "1. one\n2. two\n3. three";
        $expected = "1. one\n2. two\n3. three";
        $this->assertSame($expected, $this->plain()->render($md));
    }

    public function testLinkPlain(): void
    {
        $this->assertSame(
            'site (https://example.com)',
            $this->plain()->render('[site](https://example.com)'),
        );
    }

    public function testBlockquotePlain(): void
    {
        $out = $this->plain()->render("> hello\n> world");
        $this->assertSame("▎ hello\n▎ world", $out);
    }

    public function testHorizontalRule(): void
    {
        $out = $this->plain()->render("---");
        $this->assertSame(str_repeat('─', 40), $out);
    }

    // ---- ANSI theme assertions: not pixel-exact, just confirm SGR ----

    public function testAnsiThemeWrapsHeadingsInSgr(): void
    {
        $out = (new Renderer())->render('# Hello');
        $this->assertStringContainsString('Hello', $out);
        $this->assertStringContainsString("\x1b[", $out);
    }

    public function testAnsiThemeWrapsCode(): void
    {
        $out = (new Renderer())->render('use `foo()`');
        $this->assertStringContainsString('foo()', $out);
        $this->assertStringContainsString("\x1b[", $out);
    }

    public function testWithThemeReturnsNewInstance(): void
    {
        $a = new Renderer(Theme::ansi());
        $b = $a->withTheme(Theme::plain());
        $this->assertNotSame($a, $b);
        $this->assertSame('plain', $b->render('plain'));
    }

    public function testRenderTrimsTrailingNewlines(): void
    {
        // Two paragraphs separated by a blank line; renderer joins with
        // "\n\n" but final result has no trailing blank lines.
        $out = $this->plain()->render("one\n\ntwo");
        $this->assertSame("one\n\ntwo", $out);
    }
}
