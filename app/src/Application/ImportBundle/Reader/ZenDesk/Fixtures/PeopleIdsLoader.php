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
use Application\ImportBundle\Reader\ZenDesk\Request\ClientHelper\CoreAPI\PeopleIncrementalExport;
use Zendesk\API\ResponseException;
use DateTime;

/**
 * Class PeopleIdsLoader
 * @package Application\ImportBundle\Reader\ZenDesk\Fixtures
 */
class PeopleIdsLoader extends AbstractFixtureHelper
{
    /**
     * @var array
     */
    protected $people_ids = array();

    /**
     * @param DateTime $initial_time
     */
    public function load(DateTime $initial_time)
    {
        try {
            $people_incremental = new PeopleIncrementalExport(array(
                'start_time' => $initial_time->getTimestamp(),
            ));

            $people = $people_incremental->request($this->client);
            foreach($people->users as $person) {
                $this->people_ids[] = $person->id;
            }

        } catch (ResponseException $e) {
            $this->handleResponseException(Entity\EntityInterface::TYPE_PERSON);
        }
    }

    /**
     * Returns a random person id
     *
     * @return int
     * @throws \RuntimeException
     */
    public function getRandomPersonId()
    {
        if (empty($this->people_ids)) {
            throw new \RuntimeException('No person found');
        }

        return $this->people_ids[rand(0, count($this->people_ids) - 1)];
    }
}
