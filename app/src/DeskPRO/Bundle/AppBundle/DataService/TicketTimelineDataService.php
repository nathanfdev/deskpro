<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketLog;
use Application\DeskPRO\ORM\EntityManager;
use DeskPRO\Component\Util\ListUtils;
use DeskPRO\Component\Util\MapUtils;
use DeskPRO\Bundle\AppBundle\Ticket\Timeline\Line;

class TicketTimelineDataService extends AbstractDataService
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param Ticket $ticket
     * @return Line\LineInterface[]
     */
    public function getUserTimeline(Ticket $ticket)
    {
        $logs = $this->getTicketLogRepo()->getLogsForTicket($ticket, array(
            'order_dir' => 'ASC',
            'types' => array('ticket_created', 'message_created', 'changed_status')
        ));

        $message_ids = ListUtils::filterOutFalsey(array_map(function(TicketLog $l) {
            if ($l->action_type == 'message_created') {
                return $l->id_after;
            }
            return null;
        }, $logs));

        if ($message_ids) {
            $messages = $this->getTicketMessageRepo()->getTicketMessages($ticket, array(
                'order' => 'ASC',
                'with_notes' => false,
                'ids' => $message_ids
            ));

            $messages = MapUtils::rekeyByProperty($messages, 'id');
        } else {
            $messages = array();
        }

        $timeline = array();

        foreach ($logs as $l) {
            switch ($l->action_type) {
                case 'ticket_created':
                    $timeline[] = new Line\TicketCreatedLine($l->person, $l->date_created);
                    break;

                case 'message_created':
                    if (isset($messages[$l->id_after])) {
                        $m = $messages[$l->id_after];
                        if ($m->person->is_agent && $m->person !== $ticket->person) {
                            $timeline[] = new Line\AgentMessageLine($m);
                        } else {
                            $timeline[] = new Line\UserMessageLine($m);
                        }
                    }
                    break;

                case 'changed_status':
                    $old_type = $this->getStatusType($l->details['old_status']);
                    $new_type = $this->getStatusType($l->details['new_status']);
                    if ($old_type != $new_type && $old_type != 'hidden') {
                        if ($new_type == 'open') {
                            $timeline[] = new Line\TicketReOpenedLine($l->person, $l->date_created);
                        } else {
                            $timeline[] = new Line\TicketClosedLine($l->person, $l->date_created);
                        }
                    }
                    break;
            }
        }

        return $timeline;
    }

    /**
     * @param string $status
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
