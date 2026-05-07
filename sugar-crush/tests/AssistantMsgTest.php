<?php

declare(strict_types=1);

namespace SugarCraft\Crush\Tests;

use SugarCraft\Core\Msg;
use SugarCraft\Crush\AssistantMsg;
use SugarCraft\Crush\Message;
use SugarCraft\Crush\Role;
use PHPUnit\Framework\TestCase;

final class AssistantMsgTest extends TestCase
{
    public function testCarriesAssistantMessage(): void
    {
        $reply = Message::assistant('hello back', 1234);
        $msg = new AssistantMsg($reply);
        $this->assertSame($reply, $msg->message);
        $this->assertSame(Role::Assistant, $msg->message->role);
        $this->assertSame('hello back', $msg->message->content);
    }

    public function testIsAMsg(): void
    {
        $msg = new AssistantMsg(Message::assistant('x', 0));
        $this->assertInstanceOf(Msg::class, $msg);
    }
}
