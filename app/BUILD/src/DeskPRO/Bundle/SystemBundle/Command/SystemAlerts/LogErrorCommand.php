<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\Command\SystemAlerts;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\PHP\ErrorEvent;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\EventLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LogErrorCommand.
 */
class LogErrorCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dp:sys:log-error')
            ->setDescription('Persists an ErrorEvent entity')
            ->addArgument('type', InputArgument::REQUIRED, 'Error type')
            ->addArgument('message', InputArgument::REQUIRED, 'Error message')
            ->addArgument('file', InputArgument::REQUIRED, 'Error file path')
            ->addArgument('line', InputArgument::REQUIRED, 'Error file path line number')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EventLogger $logger */
        $logger = $this->getContainer()->get('dp_sys.alerts.event_logger');
        $logger->log(new ErrorEvent(
            $input->getArgument('type'),
            $input->getArgument('message'),
            $input->getArgument('file'),
            $input->getArgument('line')
        ));

        return 0;
    }
}
