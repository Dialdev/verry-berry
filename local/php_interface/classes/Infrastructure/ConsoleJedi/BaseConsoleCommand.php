<?php

namespace Natix\Infrastructure\ConsoleJedi;

use Notamedia\ConsoleJedi\Application\Command\BitrixCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Базовый класс для консольных команд
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
abstract class BaseConsoleCommand extends BitrixCommand
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var int
     */
    private $startMemory;

    /**
     * @var int
     */
    private $startTime;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->output = $output;

        $this->startMemory = memory_get_usage(true);

        $this->startTime = microtime(true);
    }

    protected function lln(string $message, $options = OutputInterface::VERBOSITY_VERBOSE)
    {
        $this->output->writeln(
            sprintf('<info>%s: %s</info>: %s', $this->getName(), date('Y-m-d H:i:s'), $message),
            $options
        );
    }

    protected function endExecuteCommand($options = OutputInterface::VERBOSITY_VERBOSE)
    {
        $this->lln(
            sprintf(
                'Отработало за %.03f s, съедено памяти %.03f MB',
                microtime(true) - $this->startTime,
                ((memory_get_usage(true) - $this->startMemory) / (1024 * 1024))
            ),
            $options
        );
    }
}
