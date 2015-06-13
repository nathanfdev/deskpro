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

use Application\ImportBundle\Reader\ZenDesk\ZenDeskReaderInterface;

/**
 * ZenDesk tickets people storage
 *
 * Class TicketPeopleStorage
 * @package Application\ImportBundle\Generator\Exporter\Parser\ZenDesk
 */
class TicketPeopleStorage implements TicketPeopleStorageInterface, PeopleStorageAwareInterface
{
    /**
     * @var ZenDeskReaderInterface
     */
    private $reader;

    /**
     * @var PeopleStorage
     */
    private $people_storage;

    /**
     * @var array
     */
    private $people = array();

    /**
     * Constructor
     *
     * @param ZenDeskReaderInterface $reader
     */
    public function __construct(ZenDeskReaderInterface $reader)
    {
        $this->reader = $reader;
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
    public function loadByTickets(array $tickets)
    {
        $this->loadByIds($this->getTicketsPeopleIds($tickets));
    }

    /**
     * {@inheritdoc}
     */
    public function getPersonEmail($id)
    {
        return isset($this->people[$id]['email']) ? $this->people[$id]['email'] : null;
    }

    /**
     * Returns people from reader by ids
     *
     * @param array $ids
     */
    private function loadByIds($ids)
    {
        $request_ids = $this->people_storage ? $this->people_storage->getNotContainsIds($ids) : $ids;
        $result = $this->reader->getPeopleByIds($request_ids);

        foreach ($result as $person) {
            $this->people[$person['id']] = $person;
        }
        if ($this->people_storage) {
            $this->people_storage->addIgnoreIds($request_ids);
            $this->people_storage->addPeople($this->people);
        }
    }

    /**
     * Returns all unique people ids of the found ZenDesk tickets
     *
     * @param array $tickets
     * @return array
     */
    private function getTicketsPeopleIds(array $tickets)
    {
        $people_ids = array();
        foreach ($tickets as $ticket) {
            if (isset($ticket['submitter_id']) && $ticket['submitter_id'] > 0) {
                $people_ids[] = $ticket['submitter_id'];
            }
            if (isset($ticket['assignee_id']) && $ticket['assignee_id'] > 0) {
                $people_ids[] = $ticket['assignee_id'];
            }

            if ( ! empty($ticket['comments'])) {
                foreach ($ticket['comments'] as $comment) {
                    if (isset($comment['author_id']) && $comment['author_id'] > 0) {
                        $people_ids[] = $comment['author_id'];
                    }
                }
            }
        }

        return array_unique($people_ids);
    }
}
