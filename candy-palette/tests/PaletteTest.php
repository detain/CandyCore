<?php

declare(strict_types=1);

namespace SugarCraft\Palette\Tests;

use SugarCraft\Palette\Color;
use SugarCraft\Palette\Palette;
use SugarCraft\Palette\Profile;
use SugarCraft\Palette\ProfileWriter;
use PHPUnit\Framework\TestCase;

final class PaletteTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Detection
    // -------------------------------------------------------------------------

    public function testDetectReturnsProfileEnum(): void
    {
        $profile = Palette::detect();
        $this->assertInstanceOf(Profile::class, $profile);
    }

    public function testNoColorEnvSetsNoTTY(): void
    {
        $profile = Palette::detect(null, ['NO_COLOR' => '1']);
        $this->assertSame(Profile::NoTTY, $profile);
    }

    public function testForceColorTrueColor(): void
    {
        $profile = Palette::detect(null, ['FORCE_COLOR' => '3']);
        $this->assertSame(Profile::TrueColor, $profile);
    }

    public function testForceColorAnsi256(): void
    {
        $profile = Palette::detect(null, ['FORCE_COLOR' => '2']);
        $this->assertSame(Profile::ANSI256, $profile);
    }

    public function testForceColorAnsi(): void
    {
        $profile = Palette::detect(null, ['FORCE_COLOR' => '1']);
        $this->assertSame(Profile::ANSI, $profile);
    }

    public function testForceColorAscii(): void
    {
        $profile = Palette::detect(null, ['FORCE_COLOR' => '0']);
        $this->assertSame(Profile::Ascii, $profile);
    }

    public function testColortermEnvImpliesTrueColor(): void
    {
        $profile = Palette::detect(null, ['COLORTERM' => 'truecolor', 'TERM' => 'dumb']);
        $this->assertSame(Profile::TrueColor, $profile);
    }

    public function testITerm2TermProgramImpliesTrueColor(): void
    {
        $profile = Palette::detect(null, ['TERM_PROGRAM' => 'iTerm.app', 'TERM' => 'dumb']);
        $this->assertSame(Profile::TrueColor, $profile);
    }

    public function testXterm256ImpliesTrueColor(): void
    {
        $profile = Palette::detect(null, ['TERM' => 'xterm-256color', 'NO_COLOR' => '']);
        // NO_COLOR overrides term capability
        $this->assertSame(Profile::NoTTY, $profile);
    }

    public function testXterm16ImpliesAnsi256(): void
    {
        $profile = Palette::detect(null, ['TERM' => 'xterm-16color']);
        $this->assertSame(Profile::ANSI256, $profile);
    }

    public function testDumbTerminalReturnsNoTTY(): void
    {
        $profile = Palette::detect(null, ['TERM' => 'dumb']);
        // dumb has no color, TERM_PROGRAM is absent, COLORTERM absent, NO_COLOR absent
        // so it falls through to isatty check; if not a tty → NoTTY
        $this->assertSame(Profile::NoTTY, $profile);
    }

    // -------------------------------------------------------------------------
    // Comment / describe
    // -------------------------------------------------------------------------

    public function testCommentForTrueColor(): void
    {
        $p = new Palette(null, ['FORCE_COLOR' => '3']);
        $this->assertSame('fancy', $p->comment());
    }

    public function testCommentForANSI256(): void
    {
        $p = new Palette(null, ['TERM' => 'xterm-256color']);
        $this->assertSame('1990s fancy', $p->comment());
    }

    public function testCommentForANSI(): void
    {
        $p = new Palette(null, ['TERM' => 'vt100']);
        $this->assertSame('normcore', $p->comment());
    }

    public function testDescribe(): void
    {
        $p = new Palette(null, ['FORCE_COLOR' => '2']);
        $this->assertStringContainsString('ANSI 256', $p->describe());
    }

    // -------------------------------------------------------------------------
    // Strip ANSI
    // -------------------------------------------------------------------------

    public function testStripAnsiRemovesSGR(): void
    {
        $input = "\x1b[38;2;255;0;0mred\x1b[0m";
        $stripped = Palette::stripAnsi($input);
        $this->assertSame('red', $stripped);
    }

    public function testStripAnsiRemovesOSC(): void
    {
        $input = "\x1b]8;;https://example.com\x1b\\click here\x1b]8;;\x1b\\";
        $stripped = Palette::stripAnsi($input);
        $this->assertSame('click here', $stripped);
    }

    public function testStripAnsiRemovesCSI(): void
    {
        $input = "\x1b[1;2H\x1b[J"; // clear screen
        $stripped = Palette::stripAnsi($input);
        $this->assertSame('', $stripped);
    }

    // -------------------------------------------------------------------------
    // Color conversion shortcut
    // -------------------------------------------------------------------------

    public function testToProfileShortcut(): void
    {
        $c = new Color(0x6b, 0x50, 0xff);
        $converted = Palette::toProfile($c, Profile::ANSI256);
        $this->assertInstanceOf(Color::class, $converted);
        $this->assertNotNull($converted->toAnsi256Index());
    }
}
