<?php

namespace DeskPRO\Bundle\AppBundle\Command\ServerInfo;

use Application\DeskPRO\ServerReportFile\InfoTpl;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StatusSummaryCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dp:status-summary')
            ->setDescription('Output a summary of the helpdesk environment')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tpl = new InfoTpl();
        echo $tpl->render();

        return 0;
    }
}
