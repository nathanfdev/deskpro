<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

namespace Application\ImportBundle\Generator\Exporter\Parser\ZenDesk;

use Application\ImportBundle\Entity;
use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Generator\Exporter\Parser\SkippingException;
use Application\ImportBundle\Reader\ZenDesk\ZenDeskReaderInterface;
use DateTime;
use Exception;

/**
 * ZenDesk tickets parser
 *
 * Class Tickets
 * @package Application\ImportBundle\Generator\Exporter\Parser\ZenDesk
 */
final class Tickets extends AbstractParser
{
    const STATUS_NEW      = 'new';
    const STATUS_OPEN     = 'open';
    const STATUS_PENDING  = 'pending';
    const STATUS_HOLD     = 'hold';
    const STATUS_SOLVED   = 'solved';
    const STATUS_CLOSED   = 'closed';
    const STATUS_DELETED  = 'deleted';

    const PRIORITY_URGENT = 'urgent';
    const PRIORITY_HIGH   = 'high';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_LOW    = 'low';

    /**
     * @var TicketPeopleStorage
     */
    private $tickets_people;

    /**
     * @var int
     */
    private $count = 0;

    /**
     * Constructor
     *
     * @param ZenDeskReaderInterface       $reader
     * @param TicketPeopleStorageInterface $people_storage
     */
    public function __construct(ZenDeskReaderInterface $reader, TicketPeopleStorageInterface $people_storage)
    {
        parent::__construct($reader);
        $this->tickets_people = $people_storage;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_TICKET;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $tickets = $this->getTickets();

        $collection = new Entity\Collection();
        $collection->setExpectedCount(count($tickets));

        foreach ($tickets as $num => $ticket) {
            $this->advanceProgressBar();
            $tid = @$ticket['id'] ?: '?';

            try {
                $entity = $this->exportTicket($ticket);
                if ($entity) {
                    $collection->attach($entity);
                } else {
                    $this->logDebugInfo(sprintf("[ZDTicket #%s] Invalid ticket entity", $tid), $ticket);
                    $this->logWarning(sprintf('[ZDTicket #%s] Invalid ticket record found (Skipping): Could not create entity', $tid));
                }

            } catch (SkippingException $e) {
                $this->logError(sprintf('[ZDTicket #%s] Invalid ticket record found (Skipping): %s', $tid, $e->getMessage()));

            } catch (\Exception $e) {
                $this->logDebugException(sprintf("Exception with ticket %d", $tid), $e, $ticket);
                $this->logError(sprintf('[ZDTicket #%s] Invalid ticket record found (Skipping): Unknown error: %s', $tid, $e->getMessage()));
            }
        }

        return $collection;
    }

    /**
     * Returns a ticket entity
     *
     * @param array $ticket
     *
     * @return Entity\Ticket
     * @throws SkippingException
     */
    private function exportTicket(array $ticket)
    {
        if ($this->isTicketValid($ticket)) {
            $person_email = $this->tickets_people->getPersonEmail($ticket['submitter_id']);
            $agent_email  = $this->tickets_people->getPersonEmail($ticket['assignee_id']);

            if ( ! $person_email) {
                throw new SkippingException(sprintf('Unable to get submitter email by id %s', $ticket['submitter_id']));
            }

            $entity = new Entity\Ticket();
            $entity
                ->setRawData($ticket)
                ->setDestination('ticket_' . $ticket['id'])
                ->setOid($ticket['id'])
                ->setRef($ticket['id'])
                ->setPersonEmail($person_email)
                ->setAgentEmail($agent_email)
                ->setSubject($ticket['subject'] ? : 'No subject')
                ->setStatus($this->getStatus($ticket['status']))
                ->setOrganization($this->getOrganizationName($ticket['organization_id']))
                ->setPriority($this->exportPriority($ticket['priority']))
                ->setDateCreated($this->getFromStringOrCurrentDateTime($ticket['created_at']))
                ->addMessage($this->exportMessage($ticket, $person_email))
            ;

            switch ($ticket['status']) {
                case self::STATUS_HOLD:
                    $entity->setAsHold(true);
                    break;
                case self::STATUS_SOLVED:
                    $entity->setDateResolved(new DateTime());
                    break;
                case self::STATUS_CLOSED:
                    $entity->setDateArchived(new DateTime());
                    break;
            }

            foreach ($ticket['tags'] as $label) {
                $entity->addLabel($label);
            }

            return $entity;
        }

        return null;
    }

    /**
     * Returns a ticket priority entity
     *
     * @param string $priority
     * @return Entity\TicketPriority|null
     */
    private function exportPriority($priority)
    {
        $mapping = array(
            self::PRIORITY_URGENT => 100,
            self::PRIORITY_HIGH   => 50,
            self::PRIORITY_NORMAL => 20,
            self::PRIORITY_LOW    => 10,
        );

        if ($priority) {
            if (in_array($priority, array_keys($mapping), true)) {
                $entity = new Entity\TicketPriority();
                $entity
                    ->setDestination('priority')
                    ->setOid(0)
                    ->setTitle($priority)
                    ->setValue($mapping[$priority]);

                return $entity;

            } else {
                $this->logWarning(sprintf('Unknown priority `%s`', $priority));
            }
        }

        return null;
    }

    /**
     * Returns a ticket message entity
     *
     * @param array  $ticket
     * @param string $person_email
     *
     * @return Entity\TicketMessage
     */
    public function exportMessage(array $ticket, $person_email)
    {
        $entity = new Entity\TicketMessage();
        $entity
            ->setDestination('message_' . $ticket['id'])
            ->setOid($ticket['id'])
            ->setPersonEmail($person_email)
            ->setMessageText($ticket['description'])
            ->setDateCreated($this->getFromStringOrCurrentDateTime($ticket['created_at']));

        return $entity;
    }

    /**
     * Returns tickets
     *
     * @return array
     * @throws Exception
     */
    private function getTickets()
    {
        $this->logDebugTimeStart('getTickets', "Reading tickets batch");

        $tickets = array();
        if ($this->getBatchConfig()->getTicketsEndTime() < new DateTime('-5 minutes')) {
            if ($this->getBatchConfig()->getTicketsEndTime()) {
                $this->logDebug(sprintf("Reading from time: %s", $this->getBatchConfig()->getTicketsEndTime()->format('Y-m-d H:i:s')));
            } else {
                $this->logDebug(sprintf("Reading from time: %s", "Beginning"));
            }
            $tickets = $this->reader->getTickets($this->getBatchConfig()->getTicketsEndTime());

            if (count($tickets)) {
                $this->tickets_people->loadByTickets($tickets);
                $this->end_time = $this->reader->getTicketsEndTime($this->getBatchConfig()->getTicketsEndTime());
                if ($this->end_time == $this->getBatchConfig()->getTicketsEndTime()) {
                    $this->end_time->modify('+1 second');
                }
                $this->logDebug(sprintf("New end time: %s", $this->end_time->format('Y-m-d H:i:s')));
            } else {
                $this->tickets_people->loadByTickets(array());
                $this->logDebug(sprintf("No more records"));
            }
        } else {
            $this->logAlert('No ticket was exported due 5 minutes timeout of the last end time');
        }

        $this->logDebug(sprintf("Read %d tickets", count($tickets)));
        $this->logDebugTimeEnd('getTickets', "Done reading tickets batch");

        return $tickets;
    }

    /**
     * Check if ticket has all required columns
     *
     * @param array $ticket
     * @return bool
     */
    private function isTicketValid(array $ticket)
    {
        $columns = array(
            'id',
            'submitter_id',
            'subject',
            'description',
            'status',
            'priority',
            'organization_id',
            'created_at',
            'custom_fields',
            'tags',
        );

        return $this->hasRequiredColumns($ticket, $columns)
            && $this->isArrayColumn($ticket, 'custom_fields')
            && $this->isArrayColumn($ticket, 'tags');
    }

    /**
     * Get DeskPro status by ZenDesk status
     *
     * @param string $status
     *
     * @return string
     * @throws Exception
     */
    private function getStatus($status)
    {
        $map = array(
            self::STATUS_NEW     => DeskPROEntity\Ticket::STATUS_AWAITING_AGENT,
            self::STATUS_OPEN    => DeskPROEntity\Ticket::STATUS_AWAITING_AGENT,
            self::STATUS_PENDING => DeskPROEntity\Ticket::STATUS_AWAITING_AGENT,
            self::STATUS_HOLD    => DeskPROEntity\Ticket::STATUS_AWAITING_USER,
            self::STATUS_SOLVED  => DeskPROEntity\Ticket::STATUS_RESOLVED,
            self::STATUS_CLOSED  => DeskPROEntity\Ticket::STATUS_ARCHIVED,
            self::STATUS_DELETED => DeskPROEntity\Ticket::STATUS_HIDDEN . '.' . DeskPROEntity\Ticket::HIDDEN_STATUS_DELETED,
        );

        if (isset($map[$status])) {
            return $map[$status];
        }

        throw new Exception(sprintf('Ticket status `%s` not found', $status));
    }
}
