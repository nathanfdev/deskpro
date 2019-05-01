<?php

namespace DpScripts\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExampleCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dp:script:example')
            ->setDescription('Example custom command')
            ->addOption('name', 'm', InputOption::VALUE_REQUIRED, 'Example option', '???')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('Hello <info>%s</info>', $input->getOption('name')));
    }
}
