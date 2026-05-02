<?php

declare(strict_types=1);

namespace CandyCore\Shell\Tests\Command;

use CandyCore\Shell\Command\TableCommand;
use PHPUnit\Framework\TestCase;

final class TableCommandTest extends TestCase
{
    public function testParseCsvRows(): void
    {
        $rows = TableCommand::parseRows("a,b,c\n1,2,3\n", ',');
        $this->assertSame([['a','b','c'],['1','2','3']], $rows);
    }

    public function testParseTsvRows(): void
    {
        $rows = TableCommand::parseRows("a\tb\n1\t2", "\t");
        $this->assertSame([['a','b'],['1','2']], $rows);
    }

    public function testParseRowsHandlesQuotedFields(): void
    {
        $rows = TableCommand::parseRows('"hello, world",ok', ',');
        $this->assertSame([['hello, world', 'ok']], $rows);
    }

    public function testParseBorderNames(): void
    {
        $this->assertNull(TableCommand::parseBorder('none'));
        $this->assertNotNull(TableCommand::parseBorder('rounded'));
        $this->assertNotNull(TableCommand::parseBorder('thick'));
    }

    public function testParseBorderRejectsUnknown(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        TableCommand::parseBorder('zigzag');
    }
}
