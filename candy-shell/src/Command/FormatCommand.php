<?php

declare(strict_types=1);

namespace CandyCore\Shell\Command;

use CandyCore\Shine\Renderer;
use CandyCore\Shine\Theme;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Render a Markdown file (or stdin) as styled ANSI text using
 * {@see \CandyCore\Shine\Renderer}. Non-interactive — produces output
 * straight to stdout so it pipelines naturally:
 *
 *   $ candyshell format README.md
 *   $ git log -1 --pretty=%B | candyshell format
 */
#[AsCommand(name: 'format', description: 'Render Markdown as styled ANSI text.')]
final class FormatCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('file',  InputArgument::OPTIONAL, 'Input file. Default: stdin.')
            ->addOption('theme', null, InputOption::VALUE_REQUIRED, 'ansi | plain', 'ansi');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $input->getArgument('file');
        $raw  = is_string($file) && $file !== ''
            ? @file_get_contents($file)
            : self::readStdin();
        if (!is_string($raw)) {
            return Command::FAILURE;
        }

        $renderer = new Renderer(self::pickTheme((string) $input->getOption('theme')));
        $output->writeln($renderer->render($raw));
        return Command::SUCCESS;
    }

    public static function pickTheme(string $name): Theme
    {
        return match (strtolower($name)) {
            'ansi'        => Theme::ansi(),
            'plain', 'no' => Theme::plain(),
            default       => throw new \InvalidArgumentException("unknown theme: $name"),
        };
    }

    private static function readStdin(): string
    {
        if (!defined('STDIN') || !is_resource(STDIN) || @stream_isatty(STDIN)) {
            return '';
        }
        return (string) stream_get_contents(STDIN);
    }
}
