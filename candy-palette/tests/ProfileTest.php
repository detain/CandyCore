<?php

declare(strict_types=1);

namespace SugarCraft\Palette\Tests;

use SugarCraft\Palette\Profile;
use PHPUnit\Framework\TestCase;

final class ProfileTest extends TestCase
{
    public function testTrueColorLabel(): void
    {
        $this->assertSame('TrueColor', Profile::TrueColor->label());
    }

    public function testMaxColors(): void
    {
        $this->assertSame(16_777_216, Profile::TrueColor->maxColors());
        $this->assertSame(256, Profile::ANSI256->maxColors());
        $this->assertSame(16, Profile::ANSI->maxColors());
        $this->assertSame(2, Profile::Ascii->maxColors());
        $this->assertSame(0, Profile::NoTTY->maxColors());
    }

    public function testDegradedToChain(): void
    {
        $this->assertSame(Profile::ANSI256, Profile::TrueColor->degradedTo());
        $this->assertSame(Profile::ANSI, Profile::ANSI256->degradedTo());
        $this->assertSame(Profile::Ascii, Profile::ANSI->degradedTo());
        $this->assertSame(Profile::NoTTY, Profile::Ascii->degradedTo());
        $this->assertNull(Profile::NoTTY->degradedTo());
    }

    public function testDescription(): void
    {
        $this->assertNotEmpty(Profile::TrueColor->description());
        $this->assertNotEmpty(Profile::ANSI256->description());
        $this->assertNotEmpty(Profile::ANSI->description());
    }
}
