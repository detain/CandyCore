<?php

declare(strict_types=1);

namespace SugarCraft\Shell;

use SugarCraft\Shell\Command\ChooseCommand;
use SugarCraft\Shell\Command\ConfirmCommand;
use SugarCraft\Shell\Command\FileCommand;
use SugarCraft\Shell\Command\FilterCommand;
use SugarCraft\Shell\Command\FormatCommand;
use SugarCraft\Shell\Command\InputCommand;
use SugarCraft\Shell\Command\JoinCommand;
use SugarCraft\Shell\Command\LogCommand;
use SugarCraft\Shell\Command\PagerCommand;
use SugarCraft\Shell\Command\SpinCommand;
use SugarCraft\Shell\Command\StyleCommand;
use SugarCraft\Shell\Command\TableCommand;
use SugarCraft\Shell\Command\WriteCommand;
use Symfony\Component\Console\Application as SymfonyApplication;

/**
 * Top-level Symfony Console application registering each subcommand.
 */
final class Application extends SymfonyApplication
{
    public function __construct()
    {
        parent::__construct('candyshell', '0.4.0');
        $this->addCommands([
            new StyleCommand(),
            new ChooseCommand(),
            new InputCommand(),
            new ConfirmCommand(),
            new JoinCommand(),
            new LogCommand(),
            new TableCommand(),
            new FilterCommand(),
            new WriteCommand(),
            new FileCommand(),
            new PagerCommand(),
            new SpinCommand(),
            new FormatCommand(),
        ]);
    }
}
