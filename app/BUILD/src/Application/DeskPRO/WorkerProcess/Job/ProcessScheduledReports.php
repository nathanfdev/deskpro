<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\Entity\SavedDashboardReport;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use DeskPRO\Bundle\AppBundle\Entity\Report\ScheduledReport;
use Orb\Util\Strings;
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
        $reportSaver      = $this->getContainer()->get('reports.report_saver');
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
                $savedReport = $reportSaver->saveReport($report->getReport(), $report->getPerson());
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

        /** @var PersonRepository $personRepository */
        $personRepository = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        foreach ($scheduledReport->getSendTo() as $to) {
            $person = $personRepository->findOneByEmail($to);

            $message->setTemplate(
                'DeskPRO:emails_user:scheduled-report.html.twig',
                [
                    'person_title' => $person ? $person->getDisplayName() : Strings::getNameFromEmail($to),
                    'scheduler'    => $scheduledReport->getPerson(),
                    'frequency'    => $scheduledReport->getFrequency(),
                    'reportTitle'  => $scheduledReport->getReport()->getTitle(),
                    'link'         => $this->getContainer()->get('router')->generate(
                        'reports-interface-headless-view',
                        [
                            'id'       => $savedReport->getId(),
                            'authcode' => $savedReport->getAuthcode(),
                        ],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                ]
            );

            $message->setTo($to);
            $this->getContainer()->getMailer()->send($message);
        }
    }
}
