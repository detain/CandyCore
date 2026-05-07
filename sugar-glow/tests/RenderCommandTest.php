<?php

declare(strict_types=1);

namespace SugarCraft\Glow\Tests;

use SugarCraft\Glow\RenderCommand;
use SugarCraft\Shine\Theme;
use PHPUnit\Framework\TestCase;

final class RenderCommandTest extends TestCase
{
    public function testPickThemeAnsi(): void
    {
        $theme = RenderCommand::pickTheme('ansi');
        $this->assertInstanceOf(Theme::class, $theme);
    }

    public function testPickThemePlain(): void
    {
        $theme = RenderCommand::pickTheme('plain');
        $this->assertSame('plain', $theme->paragraph->render('plain'));
    }

    public function testPickThemeCaseInsensitive(): void
    {
        $this->assertInstanceOf(Theme::class, RenderCommand::pickTheme('ANSI'));
    }

    public function testPickThemeRejectsUnknown(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        RenderCommand::pickTheme('mystery');
    }

    public function testLoadInputReadsFile(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'glow-');
        $this->assertNotFalse($tmp);
        file_put_contents($tmp, "# Hello");
        try {
            $this->assertSame("# Hello", RenderCommand::loadInput($tmp));
        } finally {
            unlink($tmp);
        }
    }

    public function testLoadInputMissingFileReturnsNull(): void
    {
        $this->assertNull(RenderCommand::loadInput('/no/such/path/sugar-glow-test.md'));
    }

    public function testPickThemeDarkLightDraculaTokyoNightPink(): void
    {
        $this->assertInstanceOf(Theme::class, RenderCommand::pickTheme('dark'));
        $this->assertInstanceOf(Theme::class, RenderCommand::pickTheme('light'));
        $this->assertInstanceOf(Theme::class, RenderCommand::pickTheme('dracula'));
        $this->assertInstanceOf(Theme::class, RenderCommand::pickTheme('tokyo-night'));
        $this->assertInstanceOf(Theme::class, RenderCommand::pickTheme('tokyonight'));
        $this->assertInstanceOf(Theme::class, RenderCommand::pickTheme('pink'));
        // Underscores accepted as separators.
        $this->assertInstanceOf(Theme::class, RenderCommand::pickTheme('tokyo_night'));
    }

    public function testPickThemeNotty(): void
    {
        // Notty is a no-style fallback (matches plain visually).
        $theme = RenderCommand::pickTheme('notty');
        $this->assertSame('plain', $theme->paragraph->render('plain'));
    }

    public function testPickThemeEmptyDefaultsToAnsi(): void
    {
        // The CLI passes the --theme default of 'ansi', but a direct
        // empty-string call should still produce a usable Theme.
        $this->assertInstanceOf(Theme::class, RenderCommand::pickTheme(''));
    }
}
