<?php

declare(strict_types=1);

namespace CandyCore\Shine\Tests;

use CandyCore\Shine\Theme;
use PHPUnit\Framework\TestCase;

final class ThemeTest extends TestCase
{
    public function testAnsiThemeAppliesColourToHeadings(): void
    {
        $rendered = Theme::ansi()->heading1->render('Hello');
        $this->assertStringContainsString("\x1b[", $rendered);
        $this->assertStringContainsString('Hello', $rendered);
    }

    public function testPlainThemeAppliesNoStyling(): void
    {
        foreach (['heading1','heading2','bold','italic','code','codeBlock','link','blockquote','listMarker','rule'] as $field) {
            $this->assertSame('text', Theme::plain()->{$field}->render('text'));
        }
    }
}
