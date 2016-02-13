<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketLog;
use DeskPRO\Bundle\AppBundle\Ticket\Timeline\Line;
use DeskPRO\Bundle\AppBundle\Ticket\Timeline\TicketTimeline;
use DeskPRO\Component\Util\MapUtils;

class TicketTimelineDataService extends AbstractDataService
{
    /**
     * @param Ticket $ticket
     *
     * @return TicketTimeline|Line\LineInterface[]
     */
    public function getUserTimeline(Ticket $ticket, $page = 1, $per_page = 50)
    {
        $raw_logs = $this->getTicketLogRepo()->getLogsForTicket($ticket, array(
            'order_dir' => 'ASC',
            'types'     => array('ticket_created', 'message_created', 'changed_status'),
        ));

        $messages = $this->getTicketMessageRepo()->getTicketMessages($ticket, array(
            'order'      => 'ASC',
            'with_notes' => false,
        ));

        $messages = MapUtils::rekeyByProperty($messages, 'id');

        $logs_source = $this->procLogLines($ticket, $raw_logs, $messages);

        // pager. see TicketTimelinePagerfantaAdapter.
        $page_offset = ($per_page * ($page - 1));
        $logs        = array_slice($logs_source, $page_offset, $per_page);
        $timeline    = new TicketTimeline(count($logs_source));

        foreach ($logs as $l) {
            switch ($l->action_type) {
                case 'ticket_created':
                    $timeline->addLine(new Line\TicketCreatedLine($l->person, $l->date_created));
                    break;

                case 'message_created':
                    if (isset($messages[$l->id_after])) {
                        $m = $messages[$l->id_after];
                        if ($m->person->is_agent && $m->person !== $ticket->person) {
                            $timeline->addLine(new Line\AgentMessageLine($m));
                        } else {
                            $timeline->addLine(new Line\UserMessageLine($m));
                        }
                    }
                    break;

                case 'changed_status':
                    $old_type = $this->getStatusType($l->details['old_status']);
                    $new_type = $this->getStatusType($l->details['new_status']);
                    if ($old_type != $new_type && $old_type != 'hidden') {
                        if ($new_type == 'open') {
                            $timeline->addLine(new Line\TicketReOpenedLine($l->person, $l->date_created));
                        } else {
                            $timeline->addLine(new Line\TicketClosedLine($l->person, $l->date_created));
                        }
                    }
                    break;
            }
        }

        return $timeline;
    }

    /**
     * This 'corrects' mistakes in the log.
     *
     * E.g., to account for bugs or processes which might not result in a log line
     * such as a mass import, we need to make sure messages are actually in the timeline!
     *
     * @param Ticket                                      $ticekt
     * @param \Application\DeskPRO\Entity\TicketLog[]     $logs
     * @param \Application\DeskPRO\Entity\TicketMessage[] $messages
     */
    private function procLogLines(Ticket $ticket, array $logs, array $messages)
    {
        $has_created       = false;
        $messages_with_log = [];

        $use_logs = [];

        foreach ($logs as $l) {
            switch ($l->action_type) {
                case 'ticket_created':
                    $has_created = true;
                    $use_logs[]  = $l;
                    break;
                case 'message_created':
                    if (isset($messages[$l->id_after])) {
                        $messages_with_log[] = $l->id_after;
                    }
                    $use_logs[] = $l;
                    break;
                case 'changed_status':
                    $use_logs[] = $l;
                    break;
            }
        }

        if (!$has_created) {
            $l               = new TicketLog();
            $l->person       = $ticket->person;
            $l->action_type  = 'ticket_created';
            $l->date_created = $ticket->date_created;
            array_unshift($use_logs, $l);
        }

        $messages_without_log = array_diff(array_keys($messages), $messages_with_log);
        if ($messages_without_log) {
            foreach ($messages_without_log as $mid) {
                $msg = $messages[$mid];

                $l               = new TicketLog();
                $l->person       = $msg->person;
                $l->action_type  = 'message_created';
                $l->date_created = $msg->date_created;
                $l->id_after     = $msg->id;

                $use_logs[] = $l;
            }

            usort($use_logs, function ($a, $b) {
                if ($a->date_created == $b->date_created) {
                    return 0;
                }

                return $a->date_created < $b->date_created ? -1 : 1;
            });
        }

        return $use_logs;
    }

    /**
     * @param string $status
     *
     * @return string
     */
    private function getStatusType($status)
    {
        switch ($status) {
            case 'awaiting_agent':
            case 'awaiting_user':
                return 'open';
            case 'hidden':
                return 'hidden';
            default:
                return 'closed';
        }
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\TicketMessage
     */
    protected function getTicketMessageRepo()
    {
        return $this->em->getRepository('DeskPRO:TicketMessage');
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\TicketLog
     */
    protected function getTicketLogRepo()
    {
        return $this->em->getRepository('DeskPRO:TicketLog');
    }
}
