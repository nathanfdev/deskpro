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

namespace Application\ImportBundle\Generator\Exporter\Parser\DeskPRO;

use Application\DeskPRO\Entity\Person;
use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;
use Application\ImportBundle\Generator\Exporter\Parser\ParserHelperSet;
use Application\ImportBundle\Generator\Exporter\Parser\PeopleStorage;
use Application\ImportBundle\Reader\DeskPRO\DeskPROReaderInterface;

/**
 * DeskPRO people parser.
 *
 * Class People
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
     * Constructor.
     *
     * @param DeskPROReaderInterface $reader
     * @param ParserHelperSet        $helpers
     * @param PeopleStorage          $tickets_people
     */
    public function __construct(DeskPROReaderInterface $reader, ParserHelperSet $helpers, PeopleStorage $tickets_people)
    {
        parent::__construct($reader, $helpers);
        $this->people_storage = $tickets_people;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_PERSON;
    }

    /**
     * Returns current users offset.
     *
     * @return int
     */
    public function getCurrentUsersMinId()
    {
        return max($this->users_min_id, $this->getBatchConfig()->getUsersMinId());
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        if ($this->people_storage->getPeople()) {
            return count($this->people_storage->getPeople());
        }

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
            $collection            = new Entity\Collection();
            $this->entities_loaded = 0;

            do {
                $batch = $this->reader->findUsers($this->getReaderBatchSize(), $this->getCurrentUsersMinId());
                $collection->merge($this->exportBatch($batch));

                $this->users_min_id = max($this->users_min_id, $collection->getMaxOid());
                $this->entities_loaded += count($batch);
            } while (count($batch) > 0);
        }

        return $collection;
    }

    /**
     * Returns a collection of people entities.
     *
     * @param array|\Traversable $data
     *
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
     * Returns a person entity.
     *
     * @param Person $person
     *
     * @return Entity\Person
     */
    protected function exportUser(Person $person)
    {
        $entity = new Entity\Person();
        $entity
            ->setRawData($person->toBasicApiData())
            ->setDestination('user_'.$person->getId())
            ->setOid($person->getId())

            ->setName($person->getDisplayName())
            ->setFirstName($person->first_name)
            ->setLastName($person->last_name)
            ->setAsAgent($person->is_agent)
            ->setAsUser($person->is_user)
            ->setAsAdmin($person->can_admin)

            ->setOrganization($person->organization ? $person->organization->getName() : null)
            ->setOrganizationPosition($person['organization_position'])

            ->setLanguage($person->language ? $person->language['title'] : null)

            ->setTimezone($person->getDateTimezone())
            ->setDateCreated($person->getDateCreated())
            ->setAsDisabled($person->isDisabled())
            ->setAsDeleted($person->isDeleted())
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

        $custom_fields = $this->getCustomFieldsParser()->export($person->custom_data);
        foreach ($custom_fields as $custom_field) {
            $entity->addCustomField($custom_field);
        }

        return $entity;
    }
}
