<?php

namespace DeskPRO\Bundle\AppBundle\Command\Utility;

use DeskPRO\Bundle\AppBundle\Notification\Event\Helpdesk\RefreshAgentInterfaceEvent;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshAgentInterfaceCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dp:utility:refresh-agent-interface')
            ->setDescription('Forces a page refresh for all connected agents')
            ->addOption('who', null, InputOption::VALUE_REQUIRED, 'Specify your name so agents can see who is causing the refresh')
            ->addOption('message', null, InputOption::VALUE_REQUIRED, 'Specify a message so agents understand why a refresh is happening')
            ->addOption('allow-ignore', null, InputOption::VALUE_NONE, 'Allows agents to cancel the refresh')
            ->addOption('reason-code', null, InputOption::VALUE_REQUIRED, 'Set a specific reason code')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ts = microtime(true);
        $output->writeln('Sending refresh event to all agents ...');

        $this->getContainer()
            ->get('event_dispatcher')
            ->dispatch(RefreshAgentInterfaceEvent::EVENT_NAME, new RefreshAgentInterfaceEvent(
                $input->getOption('who'),
                $input->getOption('message'),
                $input->getOption('allow-ignore'),
                $input->getOption('reason-code')
            ));

        $output->writeln(sprintf('Done in %.4fs', microtime(true) - $ts));

        return 0;
    }
}
