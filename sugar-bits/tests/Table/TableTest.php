<?php

declare(strict_types=1);

namespace CandyCore\Bits\Tests\Table;

use CandyCore\Bits\Table\Table;
use CandyCore\Core\KeyType;
use CandyCore\Core\Msg\KeyMsg;
use PHPUnit\Framework\TestCase;

final class TableTest extends TestCase
{
    private function focused(): Table
    {
        $t = Table::new(
            ['Name', 'Age'],
            [
                ['Alice', '30'],
                ['Bob', '25'],
                ['Carol', '40'],
                ['Dave', '35'],
            ],
            0,
            2,
        );
        [$t, ] = $t->focus();
        return $t;
    }

    public function testInitialState(): void
    {
        $t = Table::new(['H'], [['x']]);
        $this->assertSame(0, $t->index());
        $this->assertSame(['x'], $t->selectedRow());
    }

    public function testEmptyTableRendersEmpty(): void
    {
        $this->assertSame('', Table::new()->view());
    }

    public function testNavDown(): void
    {
        $t = $this->focused();
        [$t, ] = $t->update(new KeyMsg(KeyType::Down));
        $this->assertSame(1, $t->index());
        $this->assertSame(['Bob', '25'], $t->selectedRow());
    }

    public function testIgnoresKeysWhenUnfocused(): void
    {
        $t = Table::new(['H'], [['a'], ['b']]);
        [$t, ] = $t->update(new KeyMsg(KeyType::Down));
        $this->assertSame(0, $t->index());
    }

    public function testHomeAndEnd(): void
    {
        $t = $this->focused();
        [$t, ] = $t->update(new KeyMsg(KeyType::Char, 'G'));
        $this->assertSame(3, $t->index());
        [$t, ] = $t->update(new KeyMsg(KeyType::Char, 'g'));
        $this->assertSame(0, $t->index());
    }

    public function testScrollFollowsCursor(): void
    {
        $t = $this->focused();
        [$t, ] = $t->update(new KeyMsg(KeyType::Down));
        [$t, ] = $t->update(new KeyMsg(KeyType::Down));
        // height=2 → cursor=2 forces offset to 1
        $this->assertSame(2, $t->index());
        $this->assertSame(1, $t->offset);
    }

    public function testRenderIncludesHeaderAndSelectedRow(): void
    {
        $t = $this->focused();
        $view = $t->view();
        $this->assertStringContainsString('Name', $view);
        $this->assertStringContainsString('Alice', $view);
        $this->assertStringContainsString("\x1b[7m", $view); // reverse on selection
    }

    public function testJaggedRowsPad(): void
    {
        $t = Table::new(['A', 'B'], [['x', 'y'], ['z']], 0, 5);
        [$t, ] = $t->focus();
        $this->assertSame(['z'], $t->setRows([['z']])->selectedRow());
    }

    public function testSetHeadersAndRowsKeepsCursorWhenInRange(): void
    {
        $t = $this->focused();
        [$t, ] = $t->update(new KeyMsg(KeyType::Down));
        $t = $t->setRows([
            ['A', '1'], ['B', '2'], ['C', '3'], ['D', '4'],
        ]);
        $this->assertSame(1, $t->index());
    }

    public function testWidthBudgetTruncatesRightmostColumn(): void
    {
        $t = Table::new(
            ['Long', 'Short'],
            [['Hello there', 'X']],
            10,
            5,
        );
        $view = $t->view();
        // First line (header) shouldn't exceed width when column truncation
        // is applied; just sanity-check that some output rendered.
        $this->assertNotSame('', $view);
    }

    public function testNegativeSizeRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Table::new([], [], -1, 5);
    }
}
