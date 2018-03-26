<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketLog;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\EntityRepository\TicketLog as TicketLogRepository;
use Application\DeskPRO\EntityRepository\TicketMessage as TicketMessageRepository;
use DeskPRO\Bundle\AppBundle\Ticket\Timeline\Line;
use DeskPRO\Bundle\AppBundle\Ticket\Timeline\Line\LineInterface;
use DeskPRO\Bundle\AppBundle\Ticket\Timeline\TicketTimeline;
use DeskPRO\Component\Util\MapUtils;

class TicketTimelineDataService extends AbstractDataService
{
    /**
     * @param Ticket $ticket
     *
     * @return LineInterface[]
     */
    public function getUserTimeline(Ticket $ticket, $page = 1, $per_page = 50)
    {
        $raw_logs = $this->getTicketLogRepo()->getLogsForTicket($ticket, [
            'order_dir' => 'ASC',
            'types'     => ['ticket_created', 'message_created', 'changed_status'],
        ]);

        $messages = $this->getTicketMessageRepo()->getTicketMessages($ticket, [
            'order'      => 'ASC',
            'with_notes' => false,
        ]);

        $messages = MapUtils::rekeyByProperty($messages, 'id');

        $logs_source = $this->procLogLines($ticket, $raw_logs, $messages);

        // pager. see TicketTimelinePagerfantaAdapter.
        $page_offset = ($per_page * ($page - 1));
        $logs        = array_slice($logs_source, $page_offset, $per_page);
        $timeline    = new TicketTimeline(count($logs_source));

        $have_messages = [];

        foreach ($logs as $l) {
            switch ($l->action_type) {
                case 'ticket_created':
                    $timeline->addLine(new Line\TicketCreatedLine($l->date_created, $l->person));
                    break;

                case 'message_created':
                    if (isset($messages[$l->id_after]) && !isset($have_messages[$l->id_after])) {
                        // This prevents dupe messages appearing if the log isn't correct and has
                        // dupe entries for whatever reason
                        $have_messages[$l->id_after] = true;

                        $m = $messages[$l->id_after];
                        if ($m->person && $m->person->is_agent && $m->person !== $ticket->person) {
                            $timeline->addLine(new Line\AgentMessageLine($m));
                        } else {
                            $timeline->addLine(new Line\UserMessageLine($m));
                        }
                    }
                    break;

                case 'changed_status':
                    $old_type = $this->getStatusType($l->details['old_status']);
                    $new_type = $this->getStatusType($l->details['new_status']);
                    if ($l->details['old_status'] && $old_type !== $new_type && $old_type !== 'hidden') {
                        if ($new_type == 'open') {
                            $timeline->addLine(new Line\TicketReOpenedLine($l->date_created, $l->person));
                        } else {
                            $timeline->addLine(new Line\TicketClosedLine($l->date_created, $l->person));
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
     * @param Ticket          $ticket
     * @param TicketLog[]     $logs
     * @param TicketMessage[] $messages
     *
     * @return array
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
     * @return TicketMessageRepository
     */
    protected function getTicketMessageRepo()
    {
        return $this->em->getRepository(TicketMessage::class);
    }

    /**
     * @return TicketLogRepository
     */
    protected function getTicketLogRepo()
    {
        return $this->em->getRepository(TicketLog::class);
    }
}
