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

namespace Application\ImportBundle\Reader\ZenDesk;

use Application\ImportBundle\Reader\BaseReader;
use Zendesk\API;
use DateTime;

/**
 * ZenDesk reader
 *
 * see https://developer.zendesk.com/rest_api/docs/core/introduction
 * see https://developer.zendesk.com/rest_api/docs/core/incremental_export
 * see https://support.zendesk.com/hc/en-us/articles/204232743
 *
 * Class ZenDeskReader
 * @package Application\ImportBundle\Reader\ZenDesk
 */
class ZenDeskReader extends BaseReader implements ZenDeskReaderInterface
{
    /**
     * @var Request\RequestAdapterInterface
     */
    private $adapter;

    /**
     * @var DateTime
     */
    private $initial_time;

    /**
     * Constructor
     *
     * @param Request\RequestAdapterInterface $adapter
     * @param DateTime                        $initial_time
     */
    public function __construct(Request\RequestAdapterInterface $adapter, ZenDeskConfig $config)
    {
        parent::__construct($config);
        $this->adapter      = $adapter;
        $this->initial_time = $config->getInitialTime();
    }

    /**
     * {@inheritdoc}
     */
    public function getPeopleCount(DateTime $start_time = null)
    {
        $result = $this->adapter->doPeopleIncrementalExportRequest(array(
            'start_time' => $this->getStartTimeTimestamp($start_time),
        ));

        return $result ? $result->count : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getPeople(DateTime $start_time = null)
    {
        $people = array();
        $result = $this->adapter->doPeopleIncrementalExportRequest(array(
            'start_time' => $this->getStartTimeTimestamp($start_time),
        ));

        if (is_array($result->users)) {
            foreach ($result->users as $person) {
                $people[] = $this->toArray($person);
            }
        }

        return $people;
    }

    /**
     * {@inheritdoc}
     */
    public function getPeopleEndTime(DateTime $start_time = null)
    {
        $request = $this->adapter->doPeopleIncrementalExportRequest(array(
            'start_time' => $this->getStartTimeTimestamp($start_time),
        ));

        return $this->getIncrementalEndDateTime($request);
    }

    /**
     * {@inheritdoc}
     */
    public function getPeopleByIds(array $ids)
    {
        $people    = array();
        $chunk_ids = array_chunk($ids, 100);

        foreach ($chunk_ids as $chunk_ids_batch) {
            $result = $this->adapter->doPeopleFindRequest(array('id' => $chunk_ids_batch));

            if (is_array($result->users)) {
                foreach ($result->users as $person) {
                    $people[] = $this->toArray($person);
                }
            }
        }

        return $people;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizationById($id)
    {
        $result = $this->adapter->doOrganizationFindRequest(array(
            'id' => $id,
        ));

        return $this->toArray($result->organization);
    }

    /**
     * {@inheritdoc}
     */
    public function getTicketsCount(DateTime $start_time = null)
    {
        $result = $this->adapter->doTicketsIncrementalExportRequest(array(
            'start_time' => $this->getStartTimeTimestamp($start_time),
        ));

        return $result ? $result->count : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getTickets(DateTime $start_time = null)
    {
        $tickets = array();
        $result  = $this->adapter->doTicketsIncrementalExportRequest(array(
            'start_time' => $this->getStartTimeTimestamp($start_time),
        ));

        if ($result) {
            foreach ($result->tickets as $ticket) {
                $tickets[] = $this->toArray($ticket);
            }
        }

        return $tickets;
    }

    /**
     * {@inheritdoc}
     */
    public function getTicketsEndTime(DateTime $start_time = null)
    {
        $request = $this->adapter->doPeopleIncrementalExportRequest(array(
            'start_time' => $this->getStartTimeTimestamp($start_time),
        ));

        return $this->getIncrementalEndDateTime($request);
    }

    /**
     * Converts stdClass to array
     *
     * @param \stdClass $object
     * @return array
     */
    private function toArray(\stdClass $object)
    {
        return @json_decode(json_encode($object), true);
    }

    /**
     * Returns request start time timestamp
     *
     * @param DateTime $start_time
     * @return int
     */
    private function getStartTimeTimestamp(DateTime $start_time = null)
    {
        if ($start_time && $start_time > $this->initial_time) {
            return $start_time->getTimestamp();
        }

        return $this->initial_time->getTimestamp();
    }

    /**
     * Request end time timestamp to DateTime
     *
     * @param \stdClass $request
     * @return DateTime|int
     */
    private function getIncrementalEndDateTime(\stdClass $request)
    {
        if ($request) {
            $time = new DateTime();
            $time->setTimestamp($request->end_time);

            return $time;
        }

        return 0;
    }

    /**
     * @return bool
     */
    public function isReady()
    {
        return null !== $this->getPeopleCount();
    }
}
