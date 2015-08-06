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
 * Organizations csv file parser
 *
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
        $contact_data  = $this->exportOrganizationContactData();
        $custom_fields = $this->exportOrganizationCustomFields();

        foreach ($organizations as $num => $organization) {
            $this->advanceProgressBar();

            try {
                $entity = $this->exportOrganization($organization);
                if ($entity) {
                    foreach ($contact_data as $contact) {
                        /** @var Entity\ContactData $contact */
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

                    $inline_contact_data = $this->exportInlineContactData($organization);
                    foreach ($inline_contact_data as $contact) {
                        $entity->addContact($contact);
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
                ->setDestination($this->formatDestination(self::ORGANIZATION_PREFIX, $organization_id))
                ->setName($organization['name'])
                ->setImportance($organization['importance'])
                ->setPicture($this->exportBlob(1, self::ORGANIZATION_PREFIX, $organization, 'name'))
                ->setDateCreated($this->getFromStringOrCurrentDateTime(@$organization['date_created']))
            ;

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
        $config = $this->getReaderConfig(self::FILE_ORGANIZATION_CUSTOM_FIELDS);
        return $this->exportCustomFields($config, self::ORGANIZATION_PREFIX, 'organization_id');
    }

    /**
     * Returns a collection of organization contact data
     *
     * @return Entity\Collection
     */
    private function exportOrganizationContactData()
    {
        $config = $this->getReaderConfig(self::FILE_ORGANIZATION_CONTACT_DATA);
        return $this->exportContactData($config, self::ORGANIZATION_PREFIX, 'organization_id');
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
     * Returns reader config for organization records
     *
     * @return \Application\ImportBundle\Reader\Csv\CsvConfig
     */
    private function getOrganizationReaderConfig()
    {
        return $this->getReaderConfig(self::FILE_ORGANIZATIONS);
    }
}
