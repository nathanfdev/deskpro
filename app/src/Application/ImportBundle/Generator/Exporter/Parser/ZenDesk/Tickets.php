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
use DateTime;
use Exception;

/**
 * ZenDesk tickets parser
 *
 * Class Tickets
 * @package Application\ImportBundle\Generator\Exporter\Parser\ZenDesk
 */
final class Tickets extends AbstractParser implements PeopleStorageAwareInterface
{
    const STATUS_NEW     = 'new';
    const STATUS_OPEN    = 'open';
    const STATUS_PENDING = 'pending';
    const STATUS_HOLD    = 'hold';
    const STATUS_SOLVED  = 'solved';
    const STATUS_CLOSED  = 'closed';

    /**
     * @var PeopleStorage
     */
    private $people_storage;

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
    public function setPeopleStorage(PeopleStorageInterface $storage)
    {
        $this->people_storage = $storage;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return $this->reader->getTicketsCount();
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection = new Entity\Collection();
        $tickets    = $this->reader->getTickets();
        $people     = $this->getPeople($this->getPeopleIds($tickets));

        foreach ($tickets as $num => $ticket) {
            $this->advanceProgressBar();

            if ($this->hasRequiredTicketColumns($ticket) === false) {
                $this->logWarning(sprintf('Invalid ticket record found (Skipping): %d', $num));
            } else {
                if (empty($people[$ticket['submitter_id']]['email'])) {
                    $this->logWarning(sprintf('Submitter record not found (Skipping): %d', $num));
                    continue;
                }
                if (empty($people[$ticket['assignee_id']]['email'])) {
                    $this->logWarning(sprintf('Agent record not found (Skipping): %d', $num));
                    continue;
                }

                $entity = new Entity\Ticket();
                $entity
                    ->setDestination('ticket_' . $ticket['id'])
                    ->setOid($ticket['id'])
                    ->setRef($ticket['id'])
                    ->setPersonEmail($people[$ticket['submitter_id']]['email'])
                    ->setAgentEmail($people[$ticket['assignee_id']]['email'])
                    ->setSubject($ticket['subject'])
                    ->setStatus($this->getStatus($ticket['status']))
                    ->setPriority($ticket['priority'])
                    ->setDateCreated(new DateTime($ticket['created_at']));

                foreach ($ticket['tags'] as $label) {
                    $entity->addLabel($label);
                }

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
            }
        }

        return $collection;
    }

    public function exportMessage()
    {

    }

    /**
     * Check if ticket has all required columns
     *
     * @param array $ticket
     * @return bool
     */
    private function hasRequiredTicketColumns(array $ticket)
    {
        $columns = array(
            'id',
            'submitter_id',
            'subject',
            'status',
            'priority',
            'created_at',
            'custom_fields',
            'tags',
        );

        return $this->hasRequiredColumns($ticket, $columns);
    }

    /**
     * Returns all unique people ids
     *
     * @param array $tickets
     * @return array
     */
    private function getPeopleIds(array $tickets)
    {
        $people_ids = array();
        foreach ($tickets as $ticket) {
            $people_ids[] = $ticket['submitter_id'];
            $people_ids[] = $ticket['assignee_id'];
        }

        return array_unique($people_ids);
    }

    /**
     * Returns people from reader by ids
     *
     * @param array $ids
     * @return array
     */
    private function getPeople($ids)
    {
        $result = $this->reader->getPeopleByIds($ids);
        $people = array();

        foreach ($result as $person) {
            $people[$person['id']] = $person;
        }
        if ($this->people_storage) {
            $this->people_storage->addPeople($people);
        }

        return $people;
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
        );

        if (isset($map[$status])) {
            return $map[$status];
        }

        throw new Exception(sprintf('Ticket status `%s` not found', $status));
    }
}
