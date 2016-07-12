<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\ImportBundle\Reader\ZenDesk\Fixtures;

use Application\ImportBundle\Reader\ZenDesk\ZenDeskReaderInterface;
use DateTime;
use Zendesk\API\Client;
use Zendesk\API\ResponseException;

/**
 * ZenDesk abstract fixture.
 *
 * Class AbstractFixture
 */
abstract class AbstractFixture extends AbstractFixtureHelper implements FixtureInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * Constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function create($offset, DateTime $initial_time, DateTime $end_time)
    {
        $offset = (int) $offset;

        for ($i = $offset; $i < $offset + self::COUNT; ++$i) {
            try {
                $this->logInfo(sprintf('Importing fixture `%s_%d`', $this->getEntityType(), $i));
                $this->createItem($i, $initial_time, $end_time);

                $this->logInfo(sprintf('Fixture `%s_%d` imported successfully', $this->getEntityType(), $i));
            } catch (ResponseException $e) {
                $this->handleResponseException($this->getEntityType());
            }
        }
    }

    /**
     * Create a fixture item.
     *
     * @param int      $num
     * @param DateTime $initial_time
     * @param DateTime $end_time
     */
    abstract protected function createItem($num, DateTime $initial_time, DateTime $end_time);

    /**
     * Shows error output to log.
     *
     * @param string $entity_type
     */
    protected function handleResponseException($entity_type)
    {
        $this->logWarning(sprintf(
            'Bad response, entity type=`%s`, code=`%s`, headers:',

            $entity_type,
            $this->client->getDebug()->lastResponseCode
        ));

        $debug = $this->client->getDebug();
        $this->logWarning($debug);

        if ($debug->lastResponseCode == ZenDeskReaderInterface::CODE_TOO_MANY_REQUESTS) {
            sleep(60);
        }
    }
}
