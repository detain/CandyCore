<?php

declare(strict_types=1);

namespace SugarCraft\Tetris\Tests;

use SugarCraft\Core\Msg;
use SugarCraft\Tetris\GravityMsg;
use PHPUnit\Framework\TestCase;

final class GravityMsgTest extends TestCase
{
    public function testIsAMsg(): void
    {
        $this->assertInstanceOf(Msg::class, new GravityMsg());
    }
}
