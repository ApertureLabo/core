<?php

namespace ApertureLabo\CoreBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CacheClearCommand extends Command
{
    protected static $defaultName = 'core:cache-clear';

    protected function configure(): void
    {
        $this
            ->setDescription('Clear and warmup application cache with umask() function')
            ->setHelp('Use this command to clear application cache in production environment');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        umask(0022);

        $io->writeln('Suppression du cache');
        exec('php bin/console cache:clear --no-warmup');
        $io->writeln('Réchauffement du cache');
        exec('php bin/console cache:warmup --verbose');
        $io->success('Cache supprimé et réchauffé avec succès');

        return Command::SUCCESS;
    }
}
