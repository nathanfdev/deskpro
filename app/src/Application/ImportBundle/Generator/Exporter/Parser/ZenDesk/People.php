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
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Reader\ZenDesk\TimeZoneMapper;
use DateTime;
use DateTimeZone;

/**
 * ZenDesk people parser
 *
 * see https://developer.zendesk.com/rest_api/docs/core/users#time-zone
 *
 * Class People
 * @package Application\ImportBundle\Generator\Exporter\Parser\ZenDesk
 */
final class People extends AbstractParser implements PeopleStorageAwareInterface
{
    const ROLE_END_USER = 'end-user';
    const ROLE_AGENT    = 'agent';
    const ROLE_ADMIN    = 'admin';

    /**
     * @var PeopleStorage
     */
    private $people_storage;

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
        // We could read data from ZD reader twice because of ZD reader cache support
        return count($this->getPeople());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $people = $this->getPeople();

        $collection = new Entity\Collection();
        $collection->setExpectedCount(count($people));

        foreach ($people as $num => $person) {
            $this->advanceProgressBar();
            $pid = @$person['id'] ? : '?';

            try {
                $entity = $this->exportPerson($person);
                if ($entity) {
                    $collection->attach($entity);

                } else {
                    $this->logDebugInfo(sprintf("[ZDUser #%s] Invalid user entity", $pid), $person);
                    $this->logWarning(sprintf('[ZDUser #%s] Invalid user record found (Skipping): Could not create entity', $pid));
                }

            } catch (\Exception $e) {
                $this->logDebugException(sprintf("[ZDUser #%s] Exception with user", $pid), $e, $person);
                $this->logWarning(sprintf('[ZDUser #%s] Invalid user record found (Skipping): %s', $pid, $e->getMessage()));
            }
        }

        return $collection;
    }

    /**
     * Returns a person entity
     *
     * @param array $data
     *
     * @return Entity\Person
     * @throws \RuntimeException
     */
    private function exportPerson(array $data)
    {
        $formatted = $this->formatter->format($data, array(
            'id'              => TransformerInterface::TYPE_STRING,
            'destination'     => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => 'person_',
                'ref'    => 'id',
            )),
            'name'            => TransformerInterface::TYPE_STRING,
            'email'           => TransformerInterface::TYPE_STRING,
            'time_zone'       => TransformerInterface::TYPE_STRING,
            'role'            => TransformerInterface::TYPE_STRING,
            'created_at'      => TransformerInterface::TYPE_DATE,
            'user_fields'     => TransformerInterface::TYPE_ARRAY,
            'organization_id' => TransformerInterface::TYPE_STRING,
        ));

        if ( ! $formatted['email']) {
            $this->logError(sprintf('Person #%s without email, skipping', $formatted['id']));
            return null;
        }

        $entity = new Entity\Person();
        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($formatted['id'])
            ->addEmail($formatted['email'])
            ->setName($formatted['name'])
            ->setTimezone(new DateTimeZone(TimeZoneMapper::getTimeZoneName($formatted['time_zone'])))
            ->setOrganization($this->getOrganizationName($formatted['organization_id']))
            ->setDateCreated($formatted['created_at'])
        ;

        switch ($data['role']) {
            case self::ROLE_ADMIN:
                $entity->setAsAgent(true)->setAsAdmin(true);
                break;

            case self::ROLE_AGENT:
                $entity->setAsAgent(true);
                break;

            case self::ROLE_END_USER:
                $entity->setAsUser(true);
                break;
        }

        return $entity;
    }

    /**
     * Returns a collection of people to be exported
     * Gets a collection of people from the storage if it's defined or uses the ZenDesk reader
     *
     * @return array
     */
    private function getPeople()
    {
        $this->logDebugTimeStart('getPeople', "Reading people batch");

        $people = array();
        if ($this->people_storage) {
            $people = $this->people_storage->getPeople();
        }
        if (empty($people)) {
            if ($this->getBatchConfig()->getPeopleEndTime() < new DateTime('-5 minutes')) {
                if ($this->getBatchConfig()->getPeopleEndTime()) {
                    $this->logDebug(sprintf("Reading from time: %s", $this->getBatchConfig()->getPeopleEndTime()->format('Y-m-d H:i:s')));
                } else {
                    $this->logDebug(sprintf("Reading from time: %s", "Beginning"));
                }

                $people = $this->reader->getPeople($this->getBatchConfig()->getPeopleEndTime());
                if (count($people)) {
                    $this->end_time = $this->reader->getPeopleEndTime($this->getBatchConfig()->getPeopleEndTime());
                    if ($this->end_time == $this->getBatchConfig()->getPeopleEndTime()) {
                        $this->end_time->modify('+1 second');
                    }

                    $this->logDebug(sprintf("New end time: %s", $this->end_time->format('Y-m-d H:i:s')));

                } else {
                    $this->logDebug(sprintf("No more records"));
                }

            } else {
                $this->logAlert('No person was exported due 5 minutes timeout of the last end time');
            }
        }

        $this->logDebug(sprintf("Read %d people", count($people)));
        $this->logDebugTimeEnd('getPeople', "Done reading people batch");

        return $people;
    }
}
