<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketFeedback;
use Application\DeskPRO\Entity\TicketLog;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\EntityRepository\TicketFeedback as TicketFeedbackRepository;
use Application\DeskPRO\EntityRepository\TicketLog as TicketLogRepository;
use Application\DeskPRO\EntityRepository\TicketMessage as TicketMessageRepository;
use DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval;
use DeskPRO\Bundle\AppBundle\Ticket\Timeline\Line;
use DeskPRO\Bundle\AppBundle\Ticket\Timeline\TicketTimeline;
use DeskPRO\Component\Util\MapUtils;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TicketTimelineDataService extends AbstractDataService
{
    /**
     * Set to TRUE to include ticket approval logs in timeline
     */
    const ADD_TICKET_APPROVALS_TO_TIMELINE = false;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * TicketTimelineDataService constructor.
     *
     * @param EntityManager $em
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(EntityManager $em, TokenStorageInterface $tokenStorage)
    {
        parent::__construct($em);
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param Ticket $ticket
     * @param int    $page
     * @param int    $per_page
     * @param Person $person
     *
     * @return TicketTimeline
     */
    public function getUserTimeline(Ticket $ticket, $page = 1, $per_page = 50, $person = null)
    {
        $raw_logs = $this->getTicketLogRepo()->getLogsForTicket($ticket, [
            'order_dir' => 'ASC',
            'types'     => ['ticket_created', 'message_created', 'changed_status', 'ticket_approval'],
        ]);

        $messages = $this->getTicketMessageRepo()->getTicketMessages($ticket, [
            'order'      => 'ASC',
            'with_notes' => false,
        ]);

        $messages = MapUtils::rekeyByProperty($messages, 'id');

        $approvals = [];
        if ($currentUser = $this->getCurrentUser() && self::ADD_TICKET_APPROVALS_TO_TIMELINE) {
            $approvals = $this->getTicketApprovalRepo()->getTicketApprovalsByTicketAndApprover(
                $ticket,
                $currentUser
            );
            $approvals = MapUtils::rekeyByProperty($approvals, 'id');
        }

        $logs_source = $this->procLogLines($ticket, $raw_logs, $messages);

        // pager. see TicketTimelinePagerfantaAdapter.
        $page_offset = ($per_page * ($page - 1));
        $logs        = array_slice($logs_source, $page_offset, $per_page);
        $timeline    = new TicketTimeline(count($logs_source));

        $have_messages = [];

        /** @var TicketLog $l */
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
                        if ($person) {
                            if ($person->isAgent() || $person->isOrganizationManager()) {
                                $feedback = $this->getTicketFeedbackRepo()->getFeedbackForMessage($m);
                            } else {
                                $feedback = $this->getTicketFeedbackRepo()->getFeedback($m, $person, false);
                            }
                            if ($feedback) {
                                $timeline->addLine(new Line\FeedbackRatingLine($feedback));
                            }
                        }
                    }
                    break;

                case 'changed_status':
                    $oldStatus = isset($l->details['old_status']) ? $l->details['old_status'] : null;
                    $newStatus = isset($l->details['new_status']) ? $l->details['new_status'] : null;

                    $oldType = $this->getStatusType($oldStatus);
                    $newType = $this->getStatusType($newStatus);
                    if ($oldStatus && $oldType !== $newType && $oldType !== 'hidden') {
                        if ($newType == 'open') {
                            $timeline->addLine(new Line\TicketReOpenedLine($l->date_created, $l->person));
                        } else {
                            $timeline->addLine(new Line\TicketClosedLine($l->date_created, $l->person));
                        }
                    }
                    break;
                case 'ticket_approval':
                    if (isset($approvals[$l->getIdObject()]) && self::ADD_TICKET_APPROVALS_TO_TIMELINE) {
                        $timeline->addLine(new Line\TicketApprovalLine($approvals[$l->getIdObject()], $l->person));
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
                case 'ticket_approval':
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
            case 'pending':
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

    /**
     * @return \DeskPRO\Bundle\AppBundle\Entity\Repository\TicketApprovalRepository
     */
    protected function getTicketApprovalRepo()
    {
        return $this->em->getRepository(TicketApproval::class);
    }

    /**
     * @return Person|null
     */
    protected function getCurrentUser()
    {
        if ($token = $this->tokenStorage->getToken()) {
            if ($user = $token->getUser()) {
                return $user;
            }
        }

        return null;
    }

    /**
     * @return TicketFeedbackRepository
     */
    protected function getTicketFeedbackRepo()
    {
        return $this->em->getRepository(TicketFeedback::class);
    }
}
