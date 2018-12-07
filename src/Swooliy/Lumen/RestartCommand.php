<?php

namespace Swooliy\Lumen;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;

class RestartCommand extends Command
{
    protected function configure()
    {
        
        $this
            // the name of the command (the part after "bin/console")
            ->setName('lumen:restart')

            // the short description shown while running "php bin/console list"
            ->setDescription('Restart a lumen service on swoole')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('--host, --port')
        ;

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('lumen:stop');

        $arguments = [
            'command' => 'lumen:stop',
        ];

        $stopCommandInput = new ArrayInput($arguments);

        $command->run($stopCommandInput, $output);

        sleep(3);

        $command = $this->getApplication()->find('lumen:start');
        
        $arguments = [
            'command' => 'lumen:start',
            '-d'    => null,
        ];
        
        $startCommandInput = new ArrayInput($arguments);
        $command->run($startCommandInput, $output);


    }
}