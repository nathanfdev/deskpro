<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\Entity\SavedDashboardReport;
use DeskPRO\Bundle\AppBundle\Entity\Report\ScheduledReport;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Run reports and creates SavedReport entities, so people can just view already created report.
 */
class ProcessScheduledReports extends AbstractJob
{
    const DEFAULT_INTERVAL = 900; // 15 minutes

    /**
     * @throws \Exception
     */
    public function run()
    {
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $qb = $em->createQueryBuilder();
        $qb
            ->select('sr')
            ->from(ScheduledReport::class, 'sr')
            ->where('sr.nextSendDate <= CURRENT_TIMESTAMP()')
            ->orWhere('sr.nextSendDate IS NULL')
        ;
        $scheduledReports = $qb->getQuery()->execute();
        $reportSaver      = $this->getContainer()->get('deskpro.reports.saver');
        foreach ($scheduledReports as $report) {
            /** @var ScheduledReport $report */
            $timezone = new \DateTimeZone($report->getWhenTz());
            $now      = new \DateTime('now', $timezone);

            if (!$report->getNextSendDate()) {
                $nextSendDate = $reportSaver->calculateNextSendDate(
                    $report->getWhenSetting(),
                    $report->getWhenTz(),
                    $report->getFrequency(),
                    'now'
                );
                $report->setNextSendDate($nextSendDate);
            }

            if ($report->getNextSendDate()->setTimezone($timezone) <= $now) {
                $savedReport = $reportSaver->saveReport($report->getReport());
                $this->sendProcessedReport($report, $savedReport);
            }

            $nextSendDate = $reportSaver->calculateNextSendDate(
                $report->getWhenSetting(),
                $report->getWhenTz(),
                $report->getFrequency(),
                'tomorrow'
            );

            $report->setNextSendDate($nextSendDate);
            $em->persist($report);
            $em->flush();
        }
    }

    protected function sendProcessedReport(ScheduledReport $scheduledReport, SavedDashboardReport $savedReport)
    {
        $message = $this->getContainer()->getMailer()->createMessage();
        $message->setToPerson($scheduledReport->getPerson());
        $message->setTemplate(
            'DeskPRO:emails_agent:scheduled-report.html.twig',
            [
                'person'      => $scheduledReport->getPerson(),
                'frequency'   => $scheduledReport->getFrequency(),
                'reportTitle' => $scheduledReport->getReport()->getTitle(),
                'link'        => $this->getContainer()->get('router')->generate(
                    'reports-interface-headless-view',
                    [
                        'id'       => $savedReport->getId(),
                        'authcode' => $savedReport->getAuthcode(),
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ]
        );

        $this->getContainer()->getMailer()->send($message);
    }
}
