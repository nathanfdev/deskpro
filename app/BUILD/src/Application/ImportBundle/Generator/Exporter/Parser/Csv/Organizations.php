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

namespace Application\ImportBundle\Generator\Exporter\Parser\Csv;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;
use Application\ImportBundle\Reader\Csv\CsvReaderInterface;

/**
 * Organizations csv file parser.
 *
 * Class Organizations
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
        return $this->getReaderCount(CsvReaderInterface::FILE_ORGANIZATIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($this->getReaderData(CsvReaderInterface::FILE_ORGANIZATIONS))
            ->setPrefix('CSVOrganization')
            ->setRefColumn('name')
            ->setMethod('exportOrganization')
            ->setAdvanceProgressbar(true)
        ;

        $collection    = $this->exportCollection($config);
        $contact_data  = $this->exportOrganizationContactData();
        $custom_fields = $this->exportOrganizationCustomFields();

        foreach ($collection as $organization) {
            /* @var Entity\Organization $organization */
            foreach ($contact_data as $contact) {
                if ($organization->getDestination() === $contact->getDestination()) {
                    $organization->addContact($contact);
                }
            }
            foreach ($custom_fields as $custom_field_entity) {
                if ($organization->getDestination() === $custom_field_entity->getDestination()) {
                    $organization->addCustomField($custom_field_entity);
                }
            }

            $inline_contact_data = $this->getInlineContactDataParser()->export($organization->getRawData(), $organization->getDestination());
            foreach ($inline_contact_data as $contact) {
                $organization->addContact($contact);
            }

            $inline_custom_fields = $this->getInlineCustomFieldsParser()->export($organization->getDestination(), $organization->getRawData());
            foreach ($inline_custom_fields as $custom_field_entity) {
                $organization->addCustomField($custom_field_entity);
            }
        }

        return $collection;
    }

    /**
     * Returns a organization entity.
     *
     * @param array $data
     * @param int   $num
     *
     * @return Entity\Organization|null
     */
    protected function exportOrganization(array $data, $num)
    {
        $formatted = $this->formatter->format($data, [
            'id' => TransformerConfiguration::create(TransformerInterface::TYPE_STRING, [
                'default' => 'num_'.$num,
            ]),
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, [
                'prefix' => self::ORGANIZATION_PREFIX,
                'ref'    => ['original#id', 'name'],
            ]),
            'name'         => TransformerInterface::TYPE_STRING,
            'importance'   => TransformerInterface::TYPE_STRING,
            'date_created' => TransformerInterface::TYPE_DATE,
        ]);

        $entity = new Entity\Organization();
        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($formatted['id'])
            ->setName($formatted['name'])
            ->setImportance($formatted['importance'])
            ->setPicture($this->getBlobParser()->export(1, self::ORGANIZATION_PREFIX, $data, 'name'))
            ->setDateCreated($formatted['date_created'])
        ;

        return $entity;
    }

    /**
     * Returns a collection of organization custom field data.
     *
     * @return Entity\CustomField[]|Entity\Collection
     */
    private function exportOrganizationCustomFields()
    {
        $data = $this->getReaderData(CsvReaderInterface::FILE_ORGANIZATION_CUSTOM_FIELDS);

        return $this->getMultipleCustomFieldsParser()->export($data, self::ORGANIZATION_PREFIX, 'organization_id');
    }

    /**
     * Returns a collection of organization contact data.
     *
     * @return Entity\ContactData[]|Entity\Collection
     */
    private function exportOrganizationContactData()
    {
        $data = $this->getReaderData(CsvReaderInterface::FILE_ORGANIZATION_CONTACT_DATA);

        return $this->getMultipleContactDataParser()->export($data, self::ORGANIZATION_PREFIX, 'organization_id');
    }
}
