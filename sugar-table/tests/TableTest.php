<?php

declare(strict_types=1);

namespace CandyCore\Table\Tests;

use CandyCore\Table\{Column, Row, RowData, StyledCell, Table};
use PHPUnit\Framework\TestCase;

final class TableTest extends TestCase
{
    private function makeTable(): Table
    {
        return Table::withColumns([
            Column::new('id',   'ID',     5),
            Column::new('name', 'Name',  20),
            Column::new('city', 'City',  15),
        ])->withRows([
            Row::new(RowData::from(['id' => '1', 'name' => 'Alice',   'city' => 'NYC'])),
            Row::new(RowData::from(['id' => '2', 'name' => 'Bob',     'city' => 'LA'])),
            Row::new(RowData::from(['id' => '3', 'name' => 'Carol',   'city' => 'CHI'])),
        ]);
    }

    public function testNew(): void
    {
        $t = $this->makeTable();
        $this->assertSame(3, \count($t->Columns()));
        $this->assertSame(3, $t->TotalRows());
    }

    public function testAddRow(): void
    {
        $t = $this->makeTable();
        $t = $t->addRow(Row::new(RowData::from(['id' => '4', 'name' => 'Dave', 'city' => 'HOU'])));
        $this->assertSame(4, $t->TotalRows());
    }

    public function testSortByAscending(): void
    {
        $t = $this->makeTable()->SortBy('name', ascending: true);
        $this->assertSame('Alice', $t->CurrentRowData()?->get('name'));
        $this->assertSame('Bob',   $t->pagedRows()[1]->data->get('name'));
    }

    public function testSortByDescending(): void
    {
        $t = $this->makeTable()->SortBy('name', ascending: false);
        $this->assertSame('Dave', $t->pagedRows()[0]->data->get('name'));
    }

    public function testSortToggle(): void
    {
        $t = $this->makeTable()->SortBy('name', true);
        $t = $t->SortBy('name', true);  // same key, should toggle
        $this->assertSame('Dave', $t->filteredSortedRows()[0]->data->get('name'));
    }

    public function testFilter(): void
    {
        $t = $this->makeTable()->Filter('name', 'ali');
        $this->assertSame(1, $t->TotalRows());
        $this->assertSame('Alice', $t->CurrentRowData()?->get('name'));
    }

    public function testFilterClear(): void
    {
        $t = $this->makeTable()->Filter('name', 'ali');
        $t = $t->ClearFilter('name');
        $this->assertSame(3, $t->TotalRows());
    }

    public function testFilterMultipleColumns(): void
    {
        $t = Table::withColumns([
            Column::new('name', 'Name', 10),
            Column::new('city', 'City', 10),
        ])->withRows([
            Row::new(RowData::from(['name' => 'Alice', 'city' => 'NYC'])),
            Row::new(RowData::from(['name' => 'Bob',   'city' => 'NYC'])),
            Row::new(RowData::from(['name' => 'Carol', 'city' => 'LA'])),
        ])->Filter('name', 'a')
          ->Filter('city', 'NYC');

        $this->assertSame(1, $t->TotalRows());
    }

    public function testSelectNext(): void
    {
        $t = $this->makeTable()->SelectNext();
        $this->assertSame('Bob', $t->CurrentRowData()?->get('name'));
    }

    public function testSelectPrevious(): void
    {
        $t = $this->makeTable()->SelectNext()->SelectNext()->SelectPrevious();
        $this->assertSame('Bob', $t->CurrentRowData()?->get('name'));
    }

    public function testSelectNextClampsAtEnd(): void
    {
        $t = $this->makeTable();
        for ($i = 0; $i < 20; $i++) {
            $t = $t->SelectNext();
        }
        $this->assertSame('Carol', $t->CurrentRowData()?->get('name'));
    }

    public function testPagination(): void
    {
        $t = Table::withColumns([Column::new('n', 'N', 5)])
            ->withRows(
                \array_map(
                    fn($i) => Row::new(RowData::from(['n' => (string) $i])),
                    \range(1, 50)
                )
            )
            ->withPageSize(10)
            ->withPage(2);  // 0-indexed page 2 = rows 20-29

        $this->assertSame(5, $t->TotalPages());
        $this->assertSame('20', $t->CurrentRowData()?->get('n'));
    }

    public function testNextPage(): void
    {
        $t = Table::withColumns([Column::new('n', 'N', 5)])
            ->withRows(
                \array_map(
                    fn($i) => Row::new(RowData::from(['n' => (string) $i])),
                    \range(1, 30)
                )
            )
            ->withPageSize(10)
            ->NextPage();

        $this->assertSame('11', $t->CurrentRowData()?->get('n'));
    }

    public function testPageFooter(): void
    {
        $t = Table::withColumns([Column::new('n', 'N', 5)])
            ->withRows([Row::new(RowData::from(['n' => '1']))])
            ->withPageSize(10);

        $this->assertSame('Page 1 of 1', $t->PageFooter());
    }

    public function testMissingDataIndicator(): void
    {
        $t = Table::withColumns([Column::new('id', 'ID', 5), Column::new('name', 'Name', 10)])
            ->withRows([
                Row::new(RowData::from(['id' => '1'])),  // no 'name'
            ])
            ->withMissingIndicator('<missing>');

        $view = $t->View();
        $this->assertStringContainsString('<missing>', $view);
    }

    public function testStyledCellOverridesColumnStyle(): void
    {
        $t = Table::withColumns([Column::new('id', 'ID', 5)])
            ->withRows([
                Row::new(RowData::from(['id' => StyledCell::new('X', '1;31')])),
            ]);

        $view = $t->View();
        $this->assertIsString($view);
        $this->assertStringContainsString('X', $view);
    }

    public function testZebraStriping(): void
    {
        $t = $this->makeTable()->withZebra();
        $view = $t->View();
        $this->assertIsString($view);
        $this->assertStringContainsString('Alice', $view);
    }

    public function testFrozenCols(): void
    {
        $t = $this->makeTable()->withFrozenCols([0]);
        $view = $t->View();
        $this->assertIsString($view);
        $this->assertStringContainsString('ID', $view);
    }

    public function testHorizontalScroll(): void
    {
        $t = $this->makeTable()->withScrollX(5);
        $view = $t->View();
        $this->assertIsString($view);
    }

    public function testRowStyle(): void
    {
        $t = $this->makeTable()
            ->withRows([
                Row::new(RowData::from(['id' => '1', 'name' => 'X', 'city' => 'Y']))->withStyle('1'),
            ]);

        $view = $t->View();
        $this->assertIsString($view);
    }

    public function testRowWithZebra(): void
    {
        $t = $this->makeTable()
            ->withRows([
                Row::new(RowData::from(['id' => '1', 'name' => 'X', 'city' => 'Y']))->withZebra(),
            ]);

        $this->assertTrue($t->Rows()[0]->zebra);
    }

    public function testClearSort(): void
    {
        $t = $this->makeTable()->SortBy('name', false)->ClearSort();
        $this->assertSame('Alice', $t->CurrentRowData()?->get('name'));
    }

    public function testClearAllFilters(): void
    {
        $t = $this->makeTable()
            ->Filter('name', 'ali')
            ->ClearAllFilters();

        $this->assertSame(3, $t->TotalRows());
    }

    public function testViewRendersTopAndBottomBorders(): void
    {
        $t = $this->makeTable();
        $view = $t->View();
        $this->assertStringContainsString('┌', $view);
        $this->assertStringContainsString('└', $view);
        $this->assertStringContainsString('─', $view);
    }

    public function testViewRendersHeader(): void
    {
        $t = $this->makeTable();
        $view = $t->View();
        $this->assertStringContainsString('ID', $view);
        $this->assertStringContainsString('Name', $view);
        $this->assertStringContainsString('City', $view);
    }

    public function testImmutability(): void
    {
        $a = $this->makeTable();
        $b = $a->SortBy('name', false);
        $this->assertNotSame($a, $b);
        $this->assertSame(3, $a->TotalRows());
    }
}
