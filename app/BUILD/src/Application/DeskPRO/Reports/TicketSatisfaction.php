<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Reports;

use Application\DeskPRO\App;
use Doctrine\ORM\EntityManager;
use Orb\Util\Dates;

class TicketSatisfaction
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param int $page
     *
     * @return array
     */
    public function getVarsForFeedHtmlView($page)
    {
        /*
         * @var \Application\DeskPRO\EntityRepository\TicketFeedback $repository
         */

        $page = (int) $page;
        if ($page < 1) {
            $page = 1;
        }

        $vars       = [];
        $repository = $this->em->getRepository('DeskPRO:TicketFeedback');
        $feedback   = $repository->getFeedbackForFeed($page - 1);
        $count      = $repository->getCountForPaging();
        $num_pages  = $repository->getFeedbackPagesCount();

        $vars['feedback']  = $feedback;
        $vars['count']     = $count;
        $vars['page']      = $page;
        $vars['num_pages'] = $num_pages;

        return $vars;
    }

    /**
     * @param string $date
     *
     * @return array
     */
    public function getVarsForSummaryHtmlView($date)
    {
        if (!$date) {
            $date = date('Y-m');
        }

        $dt                 = new \DateTime('now', new \DateTimeZone('UTC'));
        list($year, $month) = explode('-', $date);
        $dt->setDate($year, $month, 1);
        $dt->setTime(0, 0, 0);

        $date_start = $dt->format('Y-m-d H:i:s');
        $date_end   = $dt->setTime(23, 23, 23)->setDate(
            $year,
            $month,
            Dates::daysInMonth($month, $year)
        )->format('Y-m-d H:i:s');

        $all_feedback = App::getDb()->fetchAll(
            '
                SELECT ticket_feedback.rating, UNIX_TIMESTAMP(ticket_feedback.date_created) AS created_at, tickets_messages.person_id AS agent_id
                FROM ticket_feedback
                LEFT JOIN tickets_messages ON (tickets_messages.id = ticket_feedback.message_id)
                WHERE ticket_feedback.date_created BETWEEN ? AND ?
            ',
            [$date_start, $date_end]
        );

        $vars = [];

        $all_agents    = $this->em->getRepository('DeskPRO:Person')->getAgents();
        $first_created = $this->em->getRepository('DeskPRO:TicketFeedback')->getFirstCreatedDate();

        $days          = [];
        $days_in_month = Dates::daysInMonth($month, $year);
        $day_date      = clone $dt;

        for ($i = 1; $i <= $days_in_month; ++$i) {
            $days[]   = $day_date;
            $day_date = clone $day_date;
            $day_date->add(new \DateInterval('P1D'));
        }

        foreach ($all_agents as $agent) {
            $totals[$agent['id']] = [-1 => 0, 1 => 0, 0 => 0];
        }

        $summary = [];
        $totals  = [];

        foreach ($all_feedback as $feedback) {
            $d        = date('j', $feedback['created_at']);
            $agent_id = $feedback['agent_id'];
            $rating   = $feedback['rating'];

            if (!isset($summary[$d])) {
                $summary[$d] = [];
            }
            if (!isset($summary[$d][$agent_id])) {
                $summary[$d][$agent_id] = [];
            }

            if (!isset($summary[$d][$agent_id][$rating])) {
                $summary[$d][$agent_id][$rating] = 0;
            }

            ++$summary[$d][$agent_id][$rating];

            if (!isset($totals[$agent_id])) {
                $totals[$agent_id] = [];
            }

            if (!isset($totals[$agent_id][$rating])) {
                $totals[$agent_id][$rating] = 0;
            }

            ++$totals[$agent_id][$rating];
        }

        $vars['first_created'] = $first_created;
        $vars['agents']        = $all_agents;
        $vars['summary']       = $summary;
        $vars['totals']        = $totals;
        $vars['days']          = $days;
        $vars['view_date']     = $dt;

        return $vars;
    }
}
