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

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Parser\NoColumnException;
use Application\ImportBundle\Generator\Exporter\Parser\NotArrayException;
use Application\ImportBundle\Generator\Writer\Json\Destination;

/**
 * Organizations json file parser
 *
 * Class Organizations
 * @package Application\ImportBundle\Generator\Exporter\Parser\Json
 */
final class Organizations extends AbstractParser
{
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
        return $this->reader->getDirectoryFilesCount($this->getOrganizationsReaderConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection    = new Entity\Collection();
        $organizations = $this->reader->getData($this->getOrganizationsReaderConfig());

        foreach ($organizations as $num => $organization) {
            $this->advanceProgressBar();

            try {
                $entity = $this->exportOrganization($organization);
                if ($entity) {
                    $collection->attach($entity);
                    $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
                } else {
                    $this->logWarning(sprintf('Invalid organization record found (Skipping): %d', $num));
                }

            } catch (NoColumnException $e) {
                $this->logWarning(sprintf(
                    'Invalid organization record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));

            } catch (NotArrayException $e) {
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
     * @return Entity\News
     */
    private function exportOrganization(array $organization)
    {
        if ($this->isOrganizationValid($organization)) {
            $entity = new Entity\Organization();
            $entity
                ->setRawData($organization)
                ->setDestination($this->formatDestination('organization_', $organization['oid']))
                ->setOid($organization['oid'])
                ->setName($organization['name'])
                ->setImportance($organization['importance'])
                ->setDateCreated($this->getFromStringOrCurrentDateTime($organization['date_created']))
            ;

            if ($organization['picture']) {
                $entity->setPicture($this->exportBlob($organization['picture']));
            }

            $contact_data = $this->exportContactData($organization['contact_data']);
            foreach ($contact_data as $contact) {
                $entity->addContact($contact);
            }
            foreach ($organization['labels'] as $label) {
                $entity->addLabel($label);
            }

            $custom_fields = $this->exportCustomFields($organization['custom_fields']);
            foreach ($custom_fields as $custom_field) {
                /** @var Entity\CustomField $custom_field */
                $entity->addCustomField($custom_field);
            }

            return $entity;
        }

        return null;
    }

    /**
     * Returns a collection of organizations contact data entities
     *
     * @param array $contact_data
     * @return Entity\Collection
     */
    private function exportContactData(array $contact_data)
    {
        $collection = new Entity\Collection();
        foreach ($contact_data as $num => $contact) {
            try {
                $entity = $this->exportContact($contact);
                if ($entity) {
                    $collection->attach($entity);
                    $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
                } else {
                    $this->logWarning(sprintf('Invalid organization contact data record found (Skipping): %d', $num));
                }

            } catch (NoColumnException $e) {
                $this->logError(sprintf(
                    'Invalid organization contact data record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));

            } catch (NotArrayException $e) {
                $this->logError(sprintf(
                    'Invalid organization contact data record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));
            }
        }

        return $collection;
    }

    /**
     * Returns a organization contact data entity
     *
     * @param array $contact
     * @return Entity\ContactData|null
     *
     */
    private function exportContact(array $contact)
    {
        if ($this->isContactValid($contact)) {
            $entity = new Entity\ContactData();
            $entity
                ->setRawData($contact)
                ->setContactType($contact['contact_type'])
                ->setComment($contact['comment'])
            ;

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
     * Returns record type reader config
     *
     * @return \Application\ImportBundle\Reader\Json\JsonConfig
     */
    private function getOrganizationsReaderConfig()
    {
        return $this->getReaderConfig(Destination\DestinationInterface::ENTITY_ORGANIZATION_PATH);
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
            'oid',
            'name',
            'picture',
            'importance',
            'date_created',
            'contact_data',
        );

        return $this->hasRequiredColumns($organization, $columns)
            && $this->isArrayColumn($organization, 'contact_data')
            && $this->isArrayColumn($organization, 'labels');
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
            'contact_type',
            'comment',
        );

        return $this->hasRequiredColumns($contact, $columns);
    }
}
