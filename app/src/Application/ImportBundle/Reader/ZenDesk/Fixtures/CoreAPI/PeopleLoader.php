<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\ImportBundle\Reader\ZenDesk\Fixtures\CoreAPI;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Reader\ZenDesk\Fixtures\AbstractFixtureLoader;
use Application\ImportBundle\Reader\ZenDesk\Request\Request;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class PeopleIdsLoader.
 */
class PeopleLoader extends AbstractFixtureLoader
{
    /**
     * @var ArrayCollection
     */
    protected $people;

    /**
     * {@inheritdoc}
     */
    public function load()
    {
        $initial_time = new DateTime('-2 years');
        $response     = $this->request_adapter->doRequest(Request::createCoreAPI('Person', 'incrementalExport', array(
            'start_time' => $initial_time->getTimestamp(),
        )));

        $this->people = new ArrayCollection($this->toArray($response->users));
    }

    /**
     * Returns a random person id.
     *
     * @throws \RuntimeException
     *
     * @return int
     */
    public function getRandomPersonId()
    {
        if (null === $this->people) {
            $this->load();
        }
        if (empty($this->people)) {
            throw new \RuntimeException('No person found');
        }

        return $this->people[rand(0, count($this->people) - 1)]['id'];
    }
}
