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

namespace Application\ImportBundle\Generator\Exporter\Parser\ZenDesk;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;
use Application\ImportBundle\Generator\Exporter\Parser\SkippingException;

/**
 * ZenDesk organizations parser.
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
        // We can read data from ZD reader twice because of ZD reader cache support
        return count($this->reader->getOrganizations());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($this->reader->getOrganizations())
            ->setPrefix('ZDOrganization')
            ->setRefColumn('id')
            ->setMethod('exportOrganization')
            ->setAdvanceProgressbar(true)
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns organization entity.
     *
     * @param array $data
     *
     * @return Entity\Organization
     */
    protected function exportOrganization(array $data)
    {
        $entity    = new Entity\Organization();
        $formatted = $this->formatter->format($data, array(
            'id'          => TransformerInterface::TYPE_STRING,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix'  => $entity->getDestinationPrefix(),
                'ref'     => 'id',
            )),
            'name'                => TransformerInterface::TYPE_STRING,
            'created_at'          => TransformerInterface::TYPE_DATE,
            'tags'                => TransformerInterface::TYPE_ARRAY,
            'organization_fields' => TransformerInterface::TYPE_ARRAY,
        ));

        $entity
            ->setRawData($data)
            ->setOid($formatted['id'])
            ->setDestination($formatted['destination'])
            ->setName($formatted['name'])
            ->setDateCreated($formatted['created_at'])
            ->setImportance(1)
        ;

        foreach ($formatted['tags'] as $tag) {
            $entity->addLabel($tag);
        }
        foreach ($this->exportCustomFields($formatted) as $custom_field) {
            $entity->addCustomField($custom_field);
        }

        return $entity;
    }

    /**
     * Returns a custom field entity collection.
     *
     * @param array $person
     *
     * @return Entity\Collection|Entity\CustomField[]
     */
    protected function exportCustomFields(array $person)
    {
        $custom_fields = array();
        foreach ($person['organization_fields'] as $key => $value) {
            $custom_fields[] = array(
                'id'    => $key,
                'value' => $value,
            );
        }

        $config = new ExportCollectionConfig();
        $config
            ->setData($custom_fields)
            ->setPrefix('ZDOrganizationCustomField')
            ->setRefColumn('id')
            ->setMethod('exportCustomField')
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns a custom field entity.
     *
     * @param array $data
     *
     * @return Entity\CustomField
     */
    protected function exportCustomField($data)
    {
        $entity    = new Entity\CustomField();
        $formatted = $this->formatter->format($data, array(
            'id'          => TransformerInterface::TYPE_STRING,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix'  => $entity->getDestinationPrefix(),
                'ref'     => 'id',
            )),
            'value' => TransformerInterface::TYPE_STRING,
        ));

        $custom_def = $this->getCustomDefById($formatted['id']);
        if (!empty($custom_def['custom_field_options'])) {
            foreach ($custom_def['custom_field_options'] as $option) {
                if ($option['value'] == $formatted['value']) {
                    $formatted['value'] = $option['name'];
                }
            }
        }

        $entity
            ->setRawData($data)
            ->setOid($formatted['id'])
            ->setDestination($formatted['destination'])
            ->setKey($custom_def['title'])
            ->setValue($formatted['value'] ?: '')
        ;

        return $entity;
    }

    /**
     * Returns custom def by key.
     *
     * @param int $key
     *
     * @return array
     */
    protected function getCustomDefById($key)
    {
        $custom_defs = $this->reader->getOrganizationFields();
        foreach ($custom_defs as $custom_def) {
            if ($custom_def['key'] == $key) {
                return $custom_def;
            }
        }

        throw new SkippingException(sprintf('No custom def found with id=%s', $key), $custom_defs);
    }
}
