<?php

namespace Natix\Service\Twig\Command;

use Maximaster\Tools\Twig\BitrixLoader;
use Maximaster\Tools\Twig\TwigCacheCleaner;
use Maximaster\Tools\Twig\TwigOptionsStorage;
use Notamedia\ConsoleJedi\Application\Command\BitrixCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда для сброса кеша твига, удаляет весь кеш твига и возвращает количество удаленных файлов.
 * Если файлы отсутствуют, или происходит ошибка - выводит соответствующие сообщение
 */
class ClearTwigCacheCommand extends BitrixCommand
{
    protected function configure()
    {
        $this
            ->setName('twig:clearCache')
            ->setDescription('Сбрасывает кеш твига');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $documentRoot = realpath(dirname(__DIR__, 7));
        $twigEnv = new \Twig_Environment(
            new BitrixLoader($documentRoot),
            (new TwigOptionsStorage())->asArray()
        );
        try {
            $cacheCleaner = new TwigCacheCleaner($twigEnv);
            $count = $cacheCleaner->clearAll();
            $output->writeln(sprintf('Удалено файлов кеша - %s', $count));
        } catch (\LogicException $exception) {
            $output->writeln($exception->getMessage());
        }
    }
}
