<?php

declare(strict_types=1);

namespace CandyCore\Charts\Tests\BarChart;

use CandyCore\Charts\BarChart\Bar;
use CandyCore\Charts\BarChart\BarChart;
use PHPUnit\Framework\TestCase;

final class BarChartTest extends TestCase
{
    public function testEmptyChartIsEmpty(): void
    {
        $this->assertSame('', BarChart::new([], 10, 5)->view());
    }

    public function testHeightHonoredAndLabelsRow(): void
    {
        $out = BarChart::new([['a', 1], ['b', 2]], 5, 4)->view();
        $rows = explode("\n", $out);
        // 4 height = 3 body rows + 1 label row.
        $this->assertCount(4, $rows);
    }

    public function testTallestBarReachesTop(): void
    {
        $out = BarChart::new([['x', 0.0], ['y', 1.0]], 3, 4)->view();
        $rows = explode("\n", $out);
        // First row should contain a block where the tall bar is.
        $this->assertStringContainsString('█', $rows[0]);
    }

    public function testLabelsTruncatedToColumnWidth(): void
    {
        // Two bars in a width=4 chart: 1 col each, 1 col gap.
        $out  = BarChart::new([['alpha', 0.5], ['beta', 0.9]], 4, 3)->view();
        $rows = explode("\n", $out);
        $this->assertSame('a b', $rows[count($rows) - 1]);
    }

    public function testWithoutLabels(): void
    {
        $out  = BarChart::new([['a', 1], ['b', 2]], 5, 3)->withShowLabels(false)->view();
        $rows = explode("\n", $out);
        $this->assertCount(3, $rows);
    }

    public function testAcceptsBarObjects(): void
    {
        $bars = [new Bar('x', 0.5), new Bar('y', 1.0)];
        $out  = BarChart::new($bars, 5, 3)->view();
        $this->assertNotSame('', $out);
    }

    public function testAcceptsLabelKeyedArray(): void
    {
        $out = BarChart::new(['cpu' => 1.0, 'mem' => 0.5], 7, 3)->view();
        $rows = explode("\n", $out);
        $this->assertStringContainsString('cpu', $rows[count($rows) - 1]);
    }

    public function testNegativeSizeRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        BarChart::new([], -1, 5);
    }
}
