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

/**
 * DeskPRO people parser
 */
class People extends AbstractParser
{
    /**
     * @var int
     */
    private $users_min_id = 0;

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
        $this->entities_loaded = 0;
        $collection = new Entity\Collection();

        do {
            $batch = $this->reader->findUsers($this->getReaderBatchSize(), $this->getCurrentUsersMinId());

            foreach ($batch as $num => $person) {
                $this->advanceProgressBar();

                $entity = $this->exportUser($person);
                $this->users_min_id = $person['id'];
                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));

            }

            $this->entities_loaded += count($batch);

        } while ($batch->count());

        return $collection;
    }

    /**
     * @param Person $person
     * @return Entity\Person
     */
    private function exportUser(Person $person)
    {
        $entity = new Entity\Person();
        $entity
            ->setDestination('user_' . $person['id'])
            ->setOid($person['id'])

            ->setName($person->getDisplayName())
            ->setFirstName($person['first_name'])
            ->setLastName($person['last_name'])
            ->setAsAgent((bool) $person['is_agent'])
            ->setAsUser(!$person['is_agent'])
            ->setAsAdmin((bool) $person['can_admin'])

            ->setOrganization($person->organization['name'])
            ->setOrganizationPosition($person['organization_position'])

            ->setLanguage($person->language ? $person->language['title'] : null)
            ->setPassword($person['password'])
            ->setPasswordScheme(Entity\Person::PASSWORD_SCHEME_BCRYPT)

            ->setTimezone(new \DateTimeZone($person->timezone))
            ->setDateCreated($person['date_created'])
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
