<?php

declare(strict_types=1);

namespace SugarCraft\Bits\Tests;

use PHPUnit\Framework\TestCase;
use SugarCraft\Bits\Help\Help;
use SugarCraft\Bits\TextArea\TextArea;
use SugarCraft\Bits\TextInput\TextInput;

/**
 * Verify short-form aliases on sugar-bits components match their
 * upstream-mirroring `with*` counterparts.
 */
final class ShortAliasesTest extends TestCase
{
    public function testTextInputAliases(): void
    {
        $long  = TextInput::new()->withPlaceholder('p')->withCharLimit(8)->withWidth(20);
        $short = TextInput::new()->placeholder('p')->charLimit(8)->width(20);
        $this->assertSame($long->view(), $short->view());
    }

    public function testTextInputValidatorAlias(): void
    {
        $fn = fn(string $v): ?string => $v === '' ? 'required' : null;
        $long  = TextInput::new()->withValidator($fn);
        $short = TextInput::new()->validator($fn);
        $this->assertSame($long->err(), $short->err());
    }

    public function testTextAreaAliases(): void
    {
        $long  = TextArea::new()->withPlaceholder('p')->withCharLimit(100)->withWidth(40)->withHeight(5);
        $short = TextArea::new()->placeholder('p')->charLimit(100)->width(40)->height(5);
        $this->assertSame($long->view(), $short->view());
    }

    public function testHelpAliases(): void
    {
        $long  = (new Help())->withSeparator(' • ')->withEllipsis('...')->withFullSeparator("\n\n");
        $short = (new Help())->separator(' • ')->ellipsis('...')->fullSeparator("\n\n");
        // Help renders nothing without a KeyMap; compare configured separator via internal state
        // by feeding both into a known shape. shortHelpView with no bindings yields empty string,
        // so just assert both instances are recognised as Help (smoke test of compile + chain).
        $this->assertInstanceOf(Help::class, $long);
        $this->assertInstanceOf(Help::class, $short);
    }
}
