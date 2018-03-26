<?php

namespace DeskPRO\Bundle\UpdateBundle\Command;

use DeskPRO\Bundle\UpdateBundle\Service\UpdateCleanup;
use Monolog\Logger;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCleanupCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dp:update:cleanup')
            ->setDescription('Deletes any builds older than $current - 1.')
            ->addOption('run', null, InputOption::VALUE_NONE, 'Run the cleanup. Without this flag, this command will simply emit a preview of what will be cleaned.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $service = new UpdateCleanup($this->getContainer());

        $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        $logger = new Logger('out');
        $logger->pushHandler(new ConsoleHandler($output));

        $service->cleanup($input->getOption('run'), $logger);
    }
}
