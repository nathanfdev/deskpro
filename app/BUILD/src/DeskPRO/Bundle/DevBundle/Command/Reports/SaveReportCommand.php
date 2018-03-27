<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\DevBundle\Command\Reports;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\ReportDashboardReport;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SaveReportCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dpdev:reports:save-report')
            ->addArgument('reportId', InputOption::VALUE_REQUIRED, 'The ID of the report to save');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        App::setCurrentPerson();
        $em       = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $reportId = $input->getArgument('reportId');
        /** @var ReportDashboardReport $report */
        $report      = $em->getRepository(ReportDashboardReport::class)->find($reportId);
        $reportSaver = $this->getContainer()->get('deskpro.reports.saver');
        $savedReport = $reportSaver->saveReport($report);
        $url         = $this
            ->getContainer()
            ->get('router')
            ->generate(
                'reports-interface-headless-view',
                [
                    'id'       => $savedReport->getId(),
                    'authcode' => $savedReport->getAuthcode(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        $output->writeln($url);
    }
}
