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

namespace Application\ImportBundle\Generator\Exporter\Parser\Json;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;
use Application\ImportBundle\Reader\Json\JsonReaderInterface;

/**
 * Organizations json file parser.
 *
 * Class Organizations
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
        return $this->reader->getDirectoryFilesCount(JsonReaderInterface::ENTITY_ORGANIZATION_PATH, $this->getBatchNum());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($this->reader->getData(JsonReaderInterface::ENTITY_ORGANIZATION_PATH, $this->getBatchNum()))
            ->setPrefix('JSONOrganization')
            ->setRefColumn('oid')
            ->setMethod('exportOrganization')
            ->setAdvanceProgressbar(true)
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns a organization entity.
     *
     * @param array $data
     *
     * @return Entity\News
     */
    protected function exportOrganization(array $data)
    {
        $formatted = $this->formatter->format($data, [
            'oid'            => TransformerInterface::TYPE_STRING,
            'import_map_key' => TransformerInterface::TYPE_STRING,
            'destination'    => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, [
                'prefix' => 'organization_',
                'ref'    => 'oid',
            ]),
            'name'          => TransformerInterface::TYPE_STRING,
            'picture'       => TransformerInterface::TYPE_ARRAY,
            'importance'    => TransformerInterface::TYPE_STRING,
            'date_created'  => TransformerInterface::TYPE_DATE,
            'contact_data'  => TransformerInterface::TYPE_ARRAY,
            'custom_fields' => TransformerInterface::TYPE_ARRAY,
            'labels'        => TransformerInterface::TYPE_ARRAY,
        ]);

        $entity = new Entity\Organization();
        $entity
            ->setRawData($data)
            ->setOid($formatted['oid'])
            ->setImportMapKey($formatted['import_map_key'])
            ->setDestination($formatted['destination'])
            ->setName($formatted['name'])
            ->setImportance($formatted['importance'])
            ->setDateCreated($formatted['date_created'])
            ->setPicture($this->getBlobParser()->export($formatted['picture']))
        ;

        $contact_data = $this->getContactDataParser()->export($formatted['contact_data']);
        foreach ($contact_data as $contact) {
            $entity->addContact($contact);
        }
        foreach ($data['labels'] as $label) {
            $entity->addLabel($label);
        }

        $custom_fields = $this->getCustomFieldsParser()->export($formatted['custom_fields']);
        foreach ($custom_fields as $custom_field) {
            $entity->addCustomField($custom_field);
        }

        return $entity;
    }
}
