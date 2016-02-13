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
use Application\ImportBundle\Reader\ZenDesk\Fixtures\AbstractFixture;
use DateTime;
use Zendesk\API\Client;

/**
 * ZenDesk people fixtures.
 *
 * Class People
 */
final class People extends AbstractFixture
{
    /**
     * @var PeopleFieldsLoader
     */
    private $people_fields_loader;

    /**
     * Constructor.
     *
     * @param Client             $client
     * @param PeopleFieldsLoader $people_fields_loader
     */
    public function __construct(Client $client, PeopleFieldsLoader $people_fields_loader)
    {
        parent::__construct($client);
        $this->people_fields_loader = $people_fields_loader;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_PERSON;
    }

    /**
     * {@inheritdoc}
     */
    protected function createItem($num, DateTime $initial_time, DateTime $end_time)
    {
        $params = array(
            'name'        => 'Fake name '.$num,
            'email'       => 'fake_email_'.$num.'@domain.com',
            'role'        => 'end-user',
            'verified'    => true,
            'user_fields' => $this->people_fields_loader->getRandomFieldsValues(),
        );

        $response = $this->client->users()->create($params);
        $this->logger->debug(json_encode($response));
    }
}
