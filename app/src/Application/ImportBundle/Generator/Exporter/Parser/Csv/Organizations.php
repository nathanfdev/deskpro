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

namespace Application\ImportBundle\Generator\Exporter\Parser\Csv;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Parser\NoColumnException;

/**
 * Class Organizations
 * @package Application\ImportBundle\Generator\Exporter\Parser\Csv
 */
class Organizations extends AbstractParser
{
    const ORGANIZATION_PREFIX = 'organization_';

    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_ORGANIZATION;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return $this->getReaderCount($this->getOrganizationReaderConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection    = new Entity\Collection();

        $organizations = $this->getReaderData($this->getOrganizationReaderConfig());
        $contact_data  = $this->exportContactData();
        $custom_fields = $this->exportOrganizationCustomFields();

        foreach ($organizations as $num => $organization) {
            $this->advanceProgressBar();

            try {
                $entity = $this->exportOrganization($organization);
                if ($entity) {
                    foreach ($contact_data as $contact) {
                        /** @var Entity\OrganizationContactData $contact */
                        if ($entity->getDestination() === $contact->getDestination()) {
                            $entity->addContact($contact);
                        }
                    }
                    foreach ($custom_fields as $custom_field_entity) {
                        /** @var Entity\CustomField $custom_field_entity */
                        if ($entity->getDestination() === $custom_field_entity->getDestination()) {
                            $entity->addCustomField($custom_field_entity);
                        }
                    }

                    $inline_custom_fields = $this->exportInlineCustomFields($entity->getDestination(), $organization);
                    foreach ($inline_custom_fields as $custom_field_entity) {
                        $entity->addCustomField($custom_field_entity);
                    }

                    $collection->attach($entity);
                    $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
                } else {
                    $this->logWarning(sprintf('Invalid organization record `%d` found (Skipping)', $num));
                }

            } catch (NoColumnException $e) {
                $this->logWarning(sprintf(
                    'Invalid organization record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));
            }
        }

        return $collection;
    }

    /**
     * Returns a organization entity
     *
     * @param array $organization
     * @return Entity\Organization|null
     */
    private function exportOrganization(array $organization)
    {
        if ($this->isOrganizationValid($organization)) {
            $organization_id = isset($organization['id']) ? $organization['id'] : $organization['name'];

            $entity = new Entity\Organization();
            $entity
                ->setRawData($organization)
                ->setOid($organization_id)
                ->setDestination(self::ORGANIZATION_PREFIX . $organization_id)
                ->setName($organization['name'])
                ->setImportance($organization['importance'])
                ->setPicture($this->exportBlob(1, self::ORGANIZATION_PREFIX, $organization, 'organization_id'))
            ;

            return $entity;
        }

        return null;
    }

    /**
     * Returns a collection of organization contact data entities
     *
     * @return Entity\Collection
     */
    private function exportContactData()
    {
        $collection   = new Entity\Collection();
        $contact_data = $this->getReaderData($this->getOrganizationContactDataReaderConfig());

        foreach ($contact_data as $num => $contact) {
            try {
                $entity = $this->exportContact($contact);
                if ($entity) {
                    $collection->attach($entity);
                    $this->logInfo(sprintf('Entity `%s%s` parsed successfully!', $entity->getDestination()));
                } else {
                    $this->logWarning(sprintf('Invalid organization contact record `%d` found (Skipping)', $num));
                }

            } catch (NoColumnException $e) {
                $this->logWarning(sprintf(
                    'Invalid organization contact record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));
            }
        }

        return $collection;
    }

    /**
     * Returns an organization contact data entity
     *
     * @param array $contact
     * @return Entity\OrganizationContactData|null
     */
    private function exportContact(array $contact)
    {
        if ($this->isContactValid($contact)) {
            $entity = new Entity\OrganizationContactData();
            $entity->setContactType($contact['contact_type']);

            if (array_key_exists('comment', $contact)) {
                $entity
                    ->setRawData($contact)
                    ->setOid($contact['organization_id'])
                    ->setDestination(self::ORGANIZATION_PREFIX . $contact['organization_id'])
                    ->setComment($contact['comment'])
                ;
            }
            for ($i = 1; $i < 11; $i++) {
                $field_key = 'field_' . $i;
                $setter    = 'setField' . $i;

                if (array_key_exists($field_key, $contact)) {
                    $entity->$setter($contact[$field_key]);
                }
            }

            return $entity;
        }

        return null;
    }

    /**
     * Returns a collection of organization custom field data
     *
     * @return Entity\Collection
     */
    private function exportOrganizationCustomFields()
    {
        return $this->exportCustomFields($this->getOrganizationsCustomFieldReaderConfig(), self::ORGANIZATION_PREFIX, 'organization_id');
    }

    /**
     * Check if organization has all required columns
     *
     * @param array $organization
     * @return bool
     */
    private function isOrganizationValid(array $organization)
    {
        $columns = array(
            'name',
            'importance',
        );

        return $this->hasRequiredColumns($organization, $columns);
    }

    /**
     * Check if organization contact data has all required columns
     *
     * @param array $contact
     * @return bool
     */
    private function isContactValid(array $contact)
    {
        $columns = array(
            'organization_id',
            'contact_type',
        );

        return $this->hasRequiredColumns($contact, $columns);
    }

    /**
     * Returns reader config for organization records
     *
     * @return \Application\ImportBundle\Reader\Csv\CsvConfig
     */
    private function getOrganizationReaderConfig()
    {
        return $this->getReaderConfig(self::FILE_ORGANIZATIONS);
    }

    /**
     * Returns reader config for organization contact data records
     *
     * @return \Application\ImportBundle\Reader\Csv\CsvConfig
     */
    private function getOrganizationContactDataReaderConfig()
    {
        return $this->getReaderConfig(self::FILE_ORGANIZATIONS_CONTACT_DATA);
    }

    /**
     * Returns reader config for organization custom field records
     *
     * @return \Application\ImportBundle\Reader\Csv\CsvConfig
     */
    private function getOrganizationsCustomFieldReaderConfig()
    {
        return $this->getReaderConfig(self::FILE_ORGANIZATION_CUSTOM_FIELDS);
    }
}
