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
        return $this->reader->getPeopleCount();
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection = new Entity\Collection();
        $people     = $this->reader->getPeople();

        foreach ($people as $num => $person) {
            $this->advanceProgressBar();

            if ($this->hasRequiredPersonColumns($person) === false) {
                $this->logWarning(sprintf('Invalid person record found (Skipping): %d', $num));
            } else {
                $entity = new Entity\Person();
                $entity
                    ->setDestination('ticket_' . $person['id'])
                    ->setOid($person['id'])
                    ->setName($person['name'])
                    ->setTimezone(new DateTimeZone(TimeZoneMapper::getTimeZoneName($person['time_zone'])))
                    ->setDateCreated(new DateTime($person['created_at']));

                switch ($person['role']) {
                    case self::ROLE_ADMIN:
                        $entity
                            ->setAsAgent(true)
                            ->setAsAdmin(true);
                        break;

                    case self::ROLE_AGENT:
                        $entity->setAsAgent(true);
                        break;

                    case self::ROLE_END_USER:
                        $entity->setAsUser(true);
                        break;
                }

                $entity->addEmail($person['email']);

                $user_fields = (array)$person['user_fields'];
                foreach ($user_fields as $user_field) {
                    $entity->addCustomField($this->exportCustomField($user_field));
                }

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
            }
        }

        return $collection;
    }

    /**
     * Exports person custom field
     *
     * @param $user_field
     * @return Entity\CustomField
     */
    private function exportCustomField($user_field)
    {
        return new Entity\CustomField();
    }

    /**
     * Check if person has all required columns
     *
     * @param array $person
     * @return bool
     */
    private function hasRequiredPersonColumns(array $person)
    {
        $columns = array(
            'id',
            'name',
            'email',
            'time_zone',
            'role',
            'created_at',
            'user_fields',
        );

        return $this->hasRequiredColumns($person, $columns);
    }
}
