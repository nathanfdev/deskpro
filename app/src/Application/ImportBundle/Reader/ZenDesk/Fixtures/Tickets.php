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

namespace Application\ImportBundle\Reader\ZenDesk\Fixtures;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Reader\ZenDesk\Request\ClientHelper\PeopleIncrementalExport;
use Zendesk\API\ResponseException;
use DateTime;
use Exception;

/**
 * ZenDesk tickets fixtures
 *
 * Class Tickets
 * @package Application\ImportBundle\Reader\ZenDesk\Fixtures
 */
final class Tickets extends AbstractFixture implements FixturePrepareInterface
{
    /**
     * @var array
     */
    private $people_ids = array();

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
    public function prepare(DateTime $initial_time, DateTime $end_time)
    {
        $people_incremental = new PeopleIncrementalExport(array(
            'start_time' => $initial_time->getTimestamp(),
        ));

        try {
            $people = $people_incremental->request($this->client);
            foreach($people->users as $person) {
                $this->people_ids[] = $person->id;
            }

        } catch (ResponseException $e) {
            $this->logWarning(sprintf(
                'Unable to export %s, code `%s`, headers:',

                $this->getEntityType(),
                $this->client->getDebug()->lastResponseCode
            ));

            $this->logWarning($this->client->getDebug()->lastRequestHeaders);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createItem($prefix, DateTime $initial_time, DateTime $end_time)
    {
        $types      = array('problem', 'incident', 'question', 'task');
        $priorities = array('urgent', 'high', 'normal', 'low');
        $statuses   = array('open', 'pending', 'hold', 'solved', 'closed');

        $type   = $types[rand(0, count($types) - 1)];
        $params = array(
            'subject' => 'Fake ticket ' . $prefix,
            'comment' => array(
                'type'       => 'Comment',
                'body'       => 'Thanks for your help!',
                'public'     => true,
                'created_at' => $this->getRandomDateTime($initial_time, $end_time)->format('Y-m-d\TH:i:s\Z'),
            ),
            'type'         => $type,
            'priority'     => $priorities[rand(0, count($priorities) - 1)],
            'status'       => $statuses[rand(0, count($statuses) - 1)],
            'requester_id' => $this->getRandomPersonId(),
            'submitter_id' => $this->getRandomPersonId(),
        );

        if ($type === 'task') {
            $params['due_at'] = $this->getRandomDateTime($initial_time, $end_time)->format('Y-m-d');
        }

        $this->client->tickets()->create($params);
    }

    /**
     * Returns a random datetime
     *
     * @param DateTime $initial_time
     * @param DateTime $end_time
     *
     * @return DateTime
     */
    private function getRandomDateTime(DateTime $initial_time, DateTime $end_time)
    {
        $time = new DateTime();
        $time->setTimestamp(rand(
            $initial_time->getTimestamp(),
            $end_time->getTimestamp()
        ));

        return $time;
    }

    /**
     * Returns a random person id
     *
     * @return int
     * @throws Exception
     */
    private function getRandomPersonId()
    {
        if (empty($this->people_ids)) {
            throw new Exception('No person found');
        }

        return $this->people_ids[rand(0, count($this->people_ids) - 1)];
    }
}
