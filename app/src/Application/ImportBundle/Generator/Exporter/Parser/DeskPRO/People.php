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

namespace Application\ImportBundle\Generator\Exporter\Parser\DeskPRO;

use Application\DeskPRO\Entity\Person;
use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;
use Application\ImportBundle\Generator\Exporter\Parser\PeopleStorage;
use Application\ImportBundle\Reader\DeskPRO\DeskPROReaderInterface;

/**
 * DeskPRO people parser
 *
 * Class People
 * @package Application\ImportBundle\Generator\Exporter\Parser\DeskPRO
 */
final class People extends AbstractParser
{
    /**
     * @var int
     */
    private $users_min_id = 0;

    /**
     * @var PeopleStorage
     */
    private $people_storage;

    /**
     * Constructor
     *
     * @param DeskPROReaderInterface $reader
     * @param PeopleStorage          $people_storage
     */
    public function __construct(DeskPROReaderInterface $reader, PeopleStorage $people_storage)
    {
        parent::__construct($reader);
        $this->people_storage = $people_storage;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_PERSON;
    }

    /**
     * Returns current users offset
     *
     * @return int
     */
    public function getCurrentUsersMinId()
    {
        return $this->users_min_id ? : $this->getBatchConfig()->getUsersMinId();
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return $this->reader->getUsersCount($this->getCurrentUsersMinId());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        if (count($this->people_storage->getPeople()) > 0) {
            $collection = $this->exportBatch($this->people_storage->getPeople());
        } else {
            $collection = new Entity\Collection();
            $this->entities_loaded = 0;

            do {
                $batch = $this->reader->findUsers($this->getReaderBatchSize(), $this->getCurrentUsersMinId());
                $collection->merge($this->exportBatch($batch));

                $this->users_min_id     = max($this->users_min_id, $collection->getMaxOid());
                $this->entities_loaded += count($batch);

            } while (count($batch) > 0);
        }

        return $collection;
    }

    /**
     * Returns a collection of people entities
     *
     * @param array|\Traversable $data
     * @return Entity\Collection
     */
    protected function exportBatch($data)
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($data)
            ->setPrefix('DPUser')
            ->setRefColumn('id')
            ->setMethod('exportUser')
            ->setAdvanceProgressbar(true)
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns a person entity
     *
     * @param Person $person
     * @return Entity\Person
     */
    protected function exportUser(Person $person)
    {
        $entity = new Entity\Person();
        $entity
            ->setRawData($person->toArray())
            ->setDestination('user_' . $person->getId())
            ->setOid($person->getId())

            ->setName($person->getDisplayName())
            ->setFirstName($person['first_name'])
            ->setLastName($person['last_name'])
            ->setAsAgent((bool)$person['is_agent'])
            ->setAsUser( ! $person['is_agent'])
            ->setAsAdmin((bool)$person['can_admin'])

            ->setOrganization($person->organization ? $person->organization['name'] : null)
            ->setOrganizationPosition($person['organization_position'])

            ->setLanguage($person->language ? $person->language['title'] : null)
            ->setPassword($person['password'])
            ->setPasswordScheme(Entity\Person::PASSWORD_SCHEME_BCRYPT)

            ->setTimezone($person->getDateTimezone())
            ->setDateCreated($person->getDateCreated())
        ;

        foreach ($person->emails as $email) {
            $entity->addEmail($email['email']);
        }

        foreach ($person->usergroups as $usergroup) {
            $entity->addUserGroup($usergroup['title']);
        }

        foreach ($person->labels as $label) {
            $entity->addLabel($label['label']);
        }

        // todo custom fields

        return $entity;
    }
}
