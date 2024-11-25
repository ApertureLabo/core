<?php
// src/Command/CacheClearCommand.php

namespace ApertureLabo\CoreBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:core-cache-clear',
    description: 'Clear and warmup the application cache using the umask() function',
)]
class CacheClearCommand extends Command
{
    /* protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    } */

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->text('La commande est fonctionnelle !');
        /* 
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            $io->note(sprintf('You passed an option: %s', $input->getOption('option1')));
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.'); */

        return Command::SUCCESS;
    }
}