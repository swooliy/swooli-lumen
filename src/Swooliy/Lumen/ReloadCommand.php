<?php

namespace Swooliy\Lumen;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ReloadCommand extends Command
{
    protected function configure()
    {
        
        $this
            // the name of the command (the part after "bin/console")
            ->setName('lumen:reload')

            // the short description shown while running "php bin/console list"
            ->setDescription('Reload a lumen service on swoole')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('--host, --port')
        ;

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $root = getcwd();

        $pidFilePath = $root . "/storage/logs/pid";
        
        if (!\file_exists($pidFilePath)) {
            $output->writeln("Are you sure the lumen server is running?");
            return;
        }

        if(empty($pid = file_get_contents($pidFilePath))) {
            $output->writeln("Are you sure the lumen server is running?");
            return;
        }

        system("kill -USR1 {$pid}");



    }
}