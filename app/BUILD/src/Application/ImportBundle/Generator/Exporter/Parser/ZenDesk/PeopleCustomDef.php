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

namespace Application\ImportBundle\Generator\Exporter\Parser\ZenDesk;

use Application\DeskPRO\Entity\ImportMap;
use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;
use Application\ImportBundle\Reader\ZenDesk\FieldsHandlerClassMapper;

/**
 * ZenDesk people custom def parser.
 *
 * Class PeopleCustomDef
 */
final class PeopleCustomDef extends AbstractCustomDefParser
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_PERSON_CUSTOM_DEF;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        // We can read data from ZD reader twice because of ZD reader cache support
        return count($this->reader->getPeopleFields());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($this->reader->getPeopleFields())
            ->setPrefix('ZDPersonCustomDef')
            ->setRefColumn('id')
            ->setMethod('exportCustomDef')
            ->setAdvanceProgressbar(true)
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns person custom def entity.
     *
     * @param array $data
     *
     * @return Entity\PersonCustomDef
     */
    protected function exportCustomDef(array $data)
    {
        $entity    = $this->getDefaultCustomDefEntity();
        $formatted = $this->formatter->format($data, [
            'id'          => TransformerInterface::TYPE_STRING,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, [
                'prefix' => $entity->getDestinationPrefix(),
                'ref'    => 'id',
            ]),
            'key'                   => TransformerInterface::TYPE_STRING,
            'title'                 => TransformerInterface::TYPE_STRING,
            'description'           => TransformerInterface::TYPE_STRING,
            'active'                => TransformerInterface::TYPE_BOOLEAN,
            'custom_field_options'  => TransformerInterface::TYPE_ARRAY,
            'created_at'            => TransformerInterface::TYPE_DATE,
            'updated_at'            => TransformerInterface::TYPE_DATE,
            'regexp_for_validation' => TransformerInterface::TYPE_STRING,
            'tag'                   => TransformerInterface::TYPE_STRING,
        ]);

        $entity
            ->setRawData($data)
            ->setOid($formatted['id'])
            ->setDestination($formatted['destination'])
            ->setImportMapKey(ImportMap::TYPE_ZENDESK_USER_FIELD)
            ->setTitle($formatted['title'])
            ->setDescription($formatted['description'])
            ->setHandlerClass(FieldsHandlerClassMapper::getHandlerClass($formatted['type']))
            ->setAsEnabled($formatted['active'])
            ->setOptions($this->configureOptions($formatted))
        ;

        $custom_options = $this->exportCustomFieldOptions($formatted['custom_field_options']);
        foreach ($custom_options as $num => $option) {
            $option->setImportMapKey(ImportMap::TYPE_ZENDESK_USER_FIELD);
            $entity->addCustomDef($option);
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCustomDefEntity()
    {
        return new Entity\PersonCustomDef();
    }
}
