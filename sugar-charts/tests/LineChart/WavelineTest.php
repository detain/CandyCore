<?php

declare(strict_types=1);

namespace SugarCraft\Charts\Tests\LineChart;

use SugarCraft\Charts\LineChart\Waveline;
use PHPUnit\Framework\TestCase;

final class WavelineTest extends TestCase
{
    public function testEmptyRendersBlank(): void
    {
        $out = Waveline::new([], 5, 3)->view();
        $this->assertSame(3, substr_count($out, "\n") + 1);
    }

    public function testStraightHorizontalLine(): void
    {
        // Five points on the same y level.
        $w = Waveline::new([[0, 5], [1, 5], [2, 5], [3, 5], [4, 5]], 5, 3);
        $out = $w->view();
        $rows = explode("\n", $out);
        // Middle row should be all '*' / connectors.
        $this->assertStringContainsString('*', $out);
    }

    public function testWithXYRange(): void
    {
        $w = Waveline::new([[0, 0], [10, 100]], 11, 11)
            ->withXRange(0.0, 10.0)
            ->withYRange(0.0, 100.0);
        $out = $w->view();
        $this->assertStringContainsString('*', $out);
        // Should span from upper-left to lower-right (or vice versa) — both endpoints rendered.
        $this->assertGreaterThan(0, substr_count($out, '*'));
    }

    public function testPushAppendsPoint(): void
    {
        $w = Waveline::new([])->push(1.0, 2.0);
        $this->assertCount(1, $w->points);
        $this->assertSame(1.0, (float) $w->points[0][0]);
        $this->assertSame(2.0, (float) $w->points[0][1]);
    }

    public function testPushAllAcceptsTuples(): void
    {
        $w = Waveline::new()->pushAll([[0.0, 0.0], [1.0, 2.0], [2.0, 4.0]]);
        $this->assertSame(3, $w->count());
    }

    public function testClearEmptiesPoints(): void
    {
        $w = Waveline::new()->push(0.0, 0.0)->push(1.0, 1.0);
        $this->assertSame(2, $w->count());
        $w = $w->clear();
        $this->assertTrue($w->isEmpty());
    }

    public function testClearPreservesRange(): void
    {
        $w = Waveline::new()
            ->withXRange(-1.0, 1.0)
            ->withYRange(-2.0, 2.0)
            ->withPoint('o')
            ->push(0.0, 0.0);
        $w = $w->clear();
        $this->assertSame(-1.0, $w->xMin);
        $this->assertSame(1.0,  $w->xMax);
        $this->assertSame(-2.0, $w->yMin);
        $this->assertSame(2.0,  $w->yMax);
        $this->assertSame('o',  $w->point);
    }

    public function testWithXYRangeAggregate(): void
    {
        $w = Waveline::new()->withXYRange(-1.0, 1.0, -2.0, 2.0);
        $this->assertSame(-1.0, $w->xMin);
        $this->assertSame(1.0,  $w->xMax);
        $this->assertSame(-2.0, $w->yMin);
        $this->assertSame(2.0,  $w->yMax);
    }
}
