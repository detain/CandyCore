<?php

declare(strict_types=1);

namespace SugarCraft\Charts\Tests\Canvas;

use SugarCraft\Charts\Canvas\BrailleGrid;
use SugarCraft\Charts\Canvas\Canvas;
use PHPUnit\Framework\TestCase;

final class BrailleGridTest extends TestCase
{
    public function testDotSizeMatchesCellGeometry(): void
    {
        $g = new BrailleGrid(5, 3);
        $this->assertSame([10, 12], $g->dotSize());
    }

    public function testSetAndIsSetAreSymmetrical(): void
    {
        $g = new BrailleGrid(2, 1);
        $this->assertFalse($g->isSet(3, 2));
        $g->set(3, 2);
        $this->assertTrue($g->isSet(3, 2));
        $g->unset(3, 2);
        $this->assertFalse($g->isSet(3, 2));
    }

    public function testToggleFlipsEachCall(): void
    {
        $g = new BrailleGrid(2, 1);
        $g->toggle(0, 0);
        $this->assertTrue($g->isSet(0, 0));
        $g->toggle(0, 0);
        $this->assertFalse($g->isSet(0, 0));
    }

    public function testRunePicksCorrectGlyph(): void
    {
        $g = new BrailleGrid(1, 1);
        // All four left-column dots — U+28 47 = ⡇
        $g->set(0, 0)->set(0, 1)->set(0, 2)->set(0, 3);
        $this->assertSame("\u{2847}", $g->rune(0, 0));
    }

    public function testPaintCopiesNonEmptyCellsOnly(): void
    {
        $g = new BrailleGrid(2, 1);
        $g->set(0, 0); // fills cell 0
        $canvas = new Canvas(3, 1);
        $g->paint($canvas);
        $rendered = $canvas->view();
        // First cell has content, second is left untouched (default ' ')
        $this->assertStringContainsString("\u{2801}", $rendered);
    }

    public function testOutOfBoundsDotsAreIgnored(): void
    {
        $g = new BrailleGrid(1, 1);
        $g->set(-1, -1);
        $g->set(99, 99);
        $this->assertFalse($g->isSet(99, 99));
    }

    public function testClearWipesEverything(): void
    {
        $g = new BrailleGrid(1, 1);
        $g->set(0, 0)->set(1, 3);
        $g->clear();
        $this->assertFalse($g->isSet(0, 0));
        $this->assertFalse($g->isSet(1, 3));
    }

    public function testRejectsZeroOrNegativeDimensions(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new BrailleGrid(0, 0);
    }
}
