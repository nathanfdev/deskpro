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

namespace Application\ImportBundle\Generator\Exporter\Parser\Json;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;

/**
 * Abstract custom def json file parser.
 *
 * Class AbstractCustomDefParser
 */
abstract class AbstractCustomDefParser extends AbstractParser
{
    /**
     * Returns a collection of custom def choices.
     *
     * @param array $children
     *
     * @return Entity\Collection
     */
    protected function exportChildren(array $children)
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($children)
            ->setPrefix('JSONCustomDef')
            ->setRefColumn('oid')
            ->setMethod('exportCustomDef')
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns custom def entity.
     *
     * @param array $data
     *
     * @return Entity\AbstractCustomDef
     */
    protected function exportCustomDef(array $data)
    {
        $entity    = $this->getDefaultCustomDefEntity();
        $formatted = $this->formatter->format($data, array(
            'oid'            => TransformerInterface::TYPE_STRING,
            'import_map_key' => TransformerInterface::TYPE_STRING,
            'destination'    => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix'     => $entity->getDestinationPrefix(),
                'ref'        => 'oid',
            )),
            'title'         => TransformerInterface::TYPE_STRING,
            'description'   => TransformerInterface::TYPE_STRING,
            'handler_class' => TransformerInterface::TYPE_STRING,
            'is_enabled'    => TransformerInterface::TYPE_BOOLEAN,
            'options'       => TransformerInterface::TYPE_ARRAY,
            'children'      => TransformerInterface::TYPE_ARRAY,
        ));

        $entity
            ->setRawData($data)
            ->setOid($formatted['oid'])
            ->setImportMapKey($formatted['import_map_key'])
            ->setDestination($formatted['destination'])
            ->setTitle($formatted['title'])
            ->setDescription($formatted['description'])
            ->setHandlerClass($formatted['handler_class'])
            ->setAsEnabled($formatted['is_enabled'])
            ->setOptions($formatted['options'])
        ;

        $children = $this->exportChildren($formatted['children']);
        foreach ($children as $child) {
            $entity->addCustomDef($child);
        }

        return $entity;
    }

    /**
     * Returns empty custom def entity.
     *
     * @return Entity\AbstractCustomDef
     */
    abstract protected function getDefaultCustomDefEntity();
}
