<?php

declare(strict_types=1);

namespace CandyCore\Core\Tests\Util;

use CandyCore\Core\Util\ColorProfile;
use PHPUnit\Framework\TestCase;

final class ColorProfileTest extends TestCase
{
    public function testDumbTermIsAscii(): void
    {
        $this->assertSame(ColorProfile::Ascii, ColorProfile::detect(['TERM' => 'dumb']));
    }

    public function testEmptyTermIsAscii(): void
    {
        $this->assertSame(ColorProfile::Ascii, ColorProfile::detect([]));
    }

    public function testNoColorOverridesEverything(): void
    {
        $this->assertSame(
            ColorProfile::Ascii,
            ColorProfile::detect(['NO_COLOR' => '1', 'COLORTERM' => 'truecolor', 'TERM' => 'xterm-256color']),
        );
    }

    public function testColorTermTruecolor(): void
    {
        $this->assertSame(
            ColorProfile::TrueColor,
            ColorProfile::detect(['COLORTERM' => 'truecolor', 'TERM' => 'xterm']),
        );
    }

    public function testTerm256(): void
    {
        $this->assertSame(
            ColorProfile::Ansi256,
            ColorProfile::detect(['TERM' => 'xterm-256color']),
        );
    }

    public function testPlainXtermIsAnsi16(): void
    {
        $this->assertSame(ColorProfile::Ansi, ColorProfile::detect(['TERM' => 'xterm']));
    }

    public function testCapabilityHelpers(): void
    {
        $this->assertTrue(ColorProfile::TrueColor->supportsTrueColor());
        $this->assertTrue(ColorProfile::TrueColor->supports256());
        $this->assertTrue(ColorProfile::TrueColor->supportsAnsi());

        $this->assertFalse(ColorProfile::Ansi->supportsTrueColor());
        $this->assertFalse(ColorProfile::Ansi->supports256());
        $this->assertTrue(ColorProfile::Ansi->supportsAnsi());

        $this->assertFalse(ColorProfile::Ascii->supportsAnsi());
    }

    public function testForceColorOverridesNoTtyGate(): void
    {
        // A non-TTY stream that would otherwise return NoTty.
        $tmp = fopen('php://memory', 'r+');
        try {
            $this->assertSame(
                ColorProfile::TrueColor,
                ColorProfile::detect(['FORCE_COLOR' => '1', 'TERM' => 'xterm'], $tmp),
            );
        } finally {
            fclose($tmp);
        }
    }

    public function testNonTtyStreamYieldsNoTty(): void
    {
        $tmp = fopen('php://memory', 'r+');
        try {
            $this->assertSame(
                ColorProfile::NoTty,
                ColorProfile::detect(['TERM' => 'xterm-256color'], $tmp),
            );
        } finally {
            fclose($tmp);
        }
    }

    public function testTermProgramIterm(): void
    {
        $this->assertSame(
            ColorProfile::TrueColor,
            ColorProfile::detect(['TERM_PROGRAM' => 'iTerm.app', 'TERM' => 'xterm']),
        );
    }

    public function testTermProgramAppleTerminalIs256(): void
    {
        $this->assertSame(
            ColorProfile::Ansi256,
            ColorProfile::detect(['TERM_PROGRAM' => 'Apple_Terminal', 'TERM' => 'xterm-256color']),
        );
    }

    public function testWindowsTerminalIsTrueColor(): void
    {
        $this->assertSame(
            ColorProfile::TrueColor,
            ColorProfile::detect(['WT_SESSION' => 'abcd', 'TERM' => 'xterm']),
        );
    }

    public function testCiFallsBackToAnsi(): void
    {
        $this->assertSame(
            ColorProfile::Ansi,
            ColorProfile::detect(['CI' => 'true']),
        );
        $this->assertSame(
            ColorProfile::Ansi,
            ColorProfile::detect(['GITHUB_ACTIONS' => 'true']),
        );
    }
}
