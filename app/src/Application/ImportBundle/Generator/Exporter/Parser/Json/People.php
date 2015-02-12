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

namespace Application\ImportBundle\Generator\Exporter\Parser\Json;

use Application\ImportBundle\Generator\Exporter\Parser\NoColumnException;
use Application\ImportBundle\Generator\Exporter\Parser\NotArrayException;
use Application\ImportBundle\Generator\Writer\Json\Destination;
use Application\ImportBundle\Entity;
use DateTime;
use DateTimeZone;

/**
 * People json file parser
 *
 * Class People
 * @package Application\ImportBundle\Generator\Exporter\Parser\Json
 */
final class People extends AbstractParser
{
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
    public function getCount()
    {
        return $this->reader->getDirectoryFilesCount($this->getConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection = new Entity\Collection();
        $people     = $this->reader->getData($this->getConfig());

        foreach ($people as $num => $person) {
            $this->advanceProgressBar();

            try {
                $entity = $this->exportPerson($person);
                if ($entity) {
                    $collection->attach($entity);
                    $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
                } else {
                    $this->logWarning(sprintf('Invalid person record found (Skipping): %d', $num));
                }

            } catch (NoColumnException $e) {
                $this->logWarning(sprintf(
                    'Invalid person record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));

            } catch (NotArrayException $e) {
                $this->logWarning(sprintf(
                    'Invalid person record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));
            }
        }

        return $collection;
    }

    /**
     * Returns a person entity
     *
     * @param array $person
     * @return Entity\Person|null
     */
    private function exportPerson(array $person)
    {
        if ($this->isPersonValid($person)) {
            $entity = new Entity\Person();
            $entity
                ->setDestination('ticket_' . $person['oid'])
                ->setOid($person['oid'])
                ->setAsAgent($person['is_agent'])
                ->setAsUser($person['is_user'])
                ->setAsAdmin($person['is_admin'])
                ->setFirstName($person['first_name'])
                ->setLastName($person['last_name'])
                ->setName($person['name'])
                ->setOverrideDisplayName($person['override_display_name'])
                ->setPassword($person['password'])
                ->setPasswordScheme($person['password_scheme'])
                ->setTimezone(new DateTimeZone($person['timezone']))
                ->setDateCreated(new DateTime($person['date_created']))
                ->setLanguage($person['language'])
                ->setOrganization($person['organization'])
                ->setOrganizationPosition($person['organization_position']);

            foreach ($person['emails'] as $email) {
                $entity->addEmail($email);
            }
            foreach ($person['labels'] as $label) {
                $entity->addLabel($label);
            }
            foreach ($person['user_groups'] as $user_group) {
                $entity->addUserGroup($user_group);
            }

            $custom_fields = $this->exportCustomFields($person['custom_fields']);
            foreach ($custom_fields as $custom_field) {
                /** @var Entity\CustomField $custom_field */
                $entity->addCustomField($custom_field);
            }

            return $entity;
        }

        return null;
    }

    /**
     * Returns record type reader config
     *
     * @return \Application\ImportBundle\Reader\Json\JsonConfig
     */
    private function getConfig()
    {
        return $this->getReaderConfig(Destination\DestinationInterface::ENTITY_PERSON_PATH);
    }

    /**
     * Check if person has all required columns
     *
     * @param array $person
     * @return bool
     */
    private function isPersonValid(array $person)
    {
        $columns = array(
            'oid',
            'is_agent',
            'is_user',
            'is_admin',
            'first_name',
            'last_name',
            'name',
            'override_display_name',
            'password',
            'password_scheme',
            'timezone',
            'date_created',
            'language',
            'organization',
            'organization_position',
            'emails',
            'labels',
            'user_groups',
            'custom_fields',
        );

        return $this->hasRequiredColumns($person, $columns)
            && $this->isArrayColumn($person, 'emails')
            && $this->isArrayColumn($person, 'labels')
            && $this->isArrayColumn($person, 'custom_fields');
    }
}
