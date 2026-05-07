<?php

declare(strict_types=1);

namespace SugarCraft\Shine\Tests;

use SugarCraft\Shine\SyntaxHighlighter;
use SugarCraft\Shine\Theme;
use PHPUnit\Framework\TestCase;

final class SyntaxHighlighterTest extends TestCase
{
    public function testPhpHighlightsKeywordsStringsCommentsNumbers(): void
    {
        $code = '<?php
if ($n === 42) { return "hello"; } // hi';
        $out = SyntaxHighlighter::highlight($code, 'php', Theme::ansi());

        // Each token class wraps its match in an SGR escape; we don't
        // pin the exact code (depends on the ansi theme), just that
        // each substring has been styled.
        $this->assertMatchesRegularExpression('/\x1b\[[^m]+m\Q42\E\x1b\[0m/', $out);
        $this->assertMatchesRegularExpression('/\x1b\[[^m]+m"hello"\x1b\[0m/', $out);
        // 'if' is a keyword (bold + magenta in the ansi theme — two
        // SGR opens before the text, one reset after).
        $this->assertMatchesRegularExpression('/\x1b\[1m\x1b\[[^m]+mif\x1b\[0m/u', $out);
        $this->assertMatchesRegularExpression('/\x1b\[3m\x1b\[[^m]+m\/\/ hi\x1b\[0m/', $out);
    }

    public function testJsAliasResolves(): void
    {
        $code = "const x = 1;";
        $out  = SyntaxHighlighter::highlight($code, 'javascript', Theme::ansi());
        // 'const' is a JS keyword; it should be styled.
        $this->assertMatchesRegularExpression('/\x1b\[1m\x1b\[[^m]+mconst\x1b\[0m/', $out);
    }

    public function testJsonHighlightsLiteralsAndStrings(): void
    {
        $out = SyntaxHighlighter::highlight('{"k": null, "n": 12}', 'json', Theme::ansi());
        $this->assertStringContainsString('null',  $out);
        $this->assertStringContainsString('12',    $out);
        // Strings get coloured.
        $this->assertMatchesRegularExpression('/\x1b\[[^m]+m"k"\x1b\[0m/', $out);
    }

    public function testUnknownLanguageFallsBackToPlainCodeBlockStyle(): void
    {
        $code = 'some random text';
        $out  = SyntaxHighlighter::highlight($code, 'klingon', Theme::ansi());
        // Should not contain the keyword / number SGR pairs but should
        // still pass through the codeBlock style (faint = SGR 2).
        $this->assertStringContainsString("\x1b[2m", $out);
        $this->assertStringContainsString($code, $out);
    }

    public function testNoLanguageHintRouteEqualsCodeBlock(): void
    {
        $code = 'plain';
        // We pass an empty language to test the explicit branch — the
        // public Renderer also short-circuits to codeBlock for empty,
        // but the highlighter alone should treat it as unknown.
        $out  = SyntaxHighlighter::highlight($code, '', Theme::ansi());
        $this->assertStringContainsString($code, $out);
    }

    public function testPlainThemeProducesUnstyledOutput(): void
    {
        $out = SyntaxHighlighter::highlight('if (x) return 1;', 'php', Theme::plain());
        $this->assertSame('if (x) return 1;', $out);
    }

    public function testHighlightsAreNonOverlapping(): void
    {
        // The keyword 'true' inside a string must NOT get keyword
        // styling — string match wins.
        $out = SyntaxHighlighter::highlight('$x = "true";', 'php', Theme::ansi());
        // The 'true' keyword highlight is `\b(true)\b` wrapped with
        // SGR; if it had matched inside the string we'd see two
        // separate SGR pairs around 'true'. Assert there's exactly
        // one styled-string match covering the whole quoted token.
        $this->assertMatchesRegularExpression('/\x1b\[[^m]+m"true"\x1b\[0m/', $out);
    }
}
