<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\TicketSaveActions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Doctrine\ORM\EntityManager;

class DetectAutoresponders implements TicketSaveActionInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $db;

    /**
     * @var int
     */
    private $max_tickets;

    /**
     * @var int
     */
    private $max_tickets_time;

    /**
     * @var int
     */
    private $max_replies;

    /**
     * @var int
     */
    private $max_replies_time;

    /**
     * @param EntityManager $em
     * @param int           $max_tickets
     * @param int           $max_tickets_time
     * @param int           $max_replies
     * @param int           $max_replies_time
     */
    public function __construct(EntityManager $em, $max_tickets, $max_tickets_time, $max_replies, $max_replies_time)
    {
        $this->em               = $em;
        $this->db               = $em->getConnection();
        $this->max_tickets      = $max_tickets;
        $this->max_tickets_time = $max_tickets_time;
        $this->max_replies      = $max_replies;
        $this->max_replies_time = $max_replies_time;
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     *
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function processTicket(Ticket $ticket, ExecutorContextInterface $context)
    {
        if ($context->getEventType() == 'noop' || $context->getEventPerformer() != 'user' || $context->getEventMethod() != 'email') {
            return;
        }

        //------------------------------
        // Checking for ticket flood
        //------------------------------

        if ($this->max_tickets && $context->getEventType() == 'newticket') {
            $context->getLogger()->info(sprintf('[DetectAutoresponders] Checking %d %s for more than %d tickets in %ds', $ticket->person->id, $ticket->person->getDisplayContact(), $this->max_tickets, $this->max_tickets_time));

            if ($ticket->person->disable_autoresponses) {
                $context->getLogger()->info('[DetectAutoresponders] --> User is already an auto-responder');
            } else {
                $date_cut = date('Y-m-d H:i:s', time() - $this->max_tickets_time);
                $count    = $this->db->fetchColumn('
                    SELECT COUNT(*)
                    FROM tickets
                    WHERE person_id = ? AND date_created >= ?
                ', [$ticket->person->id, $date_cut]);

                $context->getLogger()->info(sprintf('[DetectAutoresponders] --> Count: %d', $count));

                if ($count > $this->max_tickets) {
                    $context->getLogger()->info('[DetectAutoresponders] --> Marking user as an auto-responder');
                    $ticket->person->disable_autoresponses = true;
                    $ticket->person->setDisableAutoresponses(true, 'Detected via new ticket flood');
                    $this->em->persist($ticket->person);
                }
            }
        }

        //------------------------------
        // Checking for reply flood
        //------------------------------

        if ($this->max_replies && $context->getEventType() == 'newreply') {
            $message = $ticket->getStateChangeRecorder()->getNewUserReplies();
            $message = array_pop($message);

            if (!$message) {
                return;
            }

            $context->getLogger()->info(sprintf('[DetectAutoresponders] Checking %d %s for more than %d replies in %ds', $message->person->id, $message->person->getDisplayContact(), $this->max_replies, $this->max_replies_time));

            if ($message->person->disable_autoresponses) {
                $context->getLogger()->info('[DetectAutoresponders] --> User is already an auto-responder');
            } else {
                $date_cut = date('Y-m-d H:i:s', time() - $this->max_replies_time);
                $count    = $this->db->fetchColumn('
                    SELECT COUNT(*)
                    FROM tickets_messages
                    WHERE person_id = ? AND date_created >= ?
                ', [$message->person->id, $date_cut]);

                $context->getLogger()->info(sprintf('[DetectAutoresponders] --> Count: %d', $count));

                if ($count > $this->max_replies) {
                    $context->getLogger()->info('[DetectAutoresponders] --> Marking user as an auto-responder');
                    $message->person->disable_autoresponses = true;
                    $message->person->setDisableAutoresponses(true, 'Detected via new reply flood');
                    $this->em->persist($message->person);
                }
            }
        }
    }
}
