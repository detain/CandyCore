<?php

declare(strict_types=1);

namespace CandyCore\Shell\Tests\Command;

use CandyCore\Shell\Command\FormatCommand;
use CandyCore\Shine\Theme;
use PHPUnit\Framework\TestCase;

final class FormatCommandTest extends TestCase
{
    public function testPickThemeAnsi(): void
    {
        $theme = FormatCommand::pickTheme('ansi');
        $this->assertInstanceOf(Theme::class, $theme);
    }

    public function testPickThemePlain(): void
    {
        $theme = FormatCommand::pickTheme('plain');
        $this->assertSame('plain', $theme->paragraph->render('plain'));
    }

    public function testPickThemeCaseInsensitive(): void
    {
        $this->assertInstanceOf(Theme::class, FormatCommand::pickTheme('ANSI'));
    }

    public function testPickThemeRejectsUnknown(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        FormatCommand::pickTheme('nightmare');
    }
}
