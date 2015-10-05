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

namespace Application\ImportBundle\Generator\Exporter\Parser\Json\Helper;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\AbstractParserFormatterHelper;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;

/**
 * Class Translations.
 */
class Translations extends AbstractParserFormatterHelper
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_OBJECT_LANG;
    }

    /**
     * Exports object lang entities.
     *
     * @param array $translations
     *
     * @return Entity\ObjectLang[]
     */
    public function export(array $translations)
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($translations)
            ->setPrefix('JSONTranslation')
            ->setRefColumn('oid')
            ->setMethod('exportTranslation')
        ;

        return $this->exportCollection($config);
    }

    /**
     * Exports object lang entity.
     *
     * @param array $data
     *
     * @return Entity\ObjectLang
     */
    public function exportTranslation(array $data)
    {
        $formatted = $this->formatter->format($data, array(
            'oid'         => TransformerInterface::TYPE_STRING,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix'  => 'translation_',
                'ref'     => 'oid',
            )),
            'language' => TransformerInterface::TYPE_STRING,
            'property' => TransformerInterface::TYPE_STRING,
            'value'    => TransformerInterface::TYPE_STRING,
        ));

        $entity = new Entity\ObjectLang();
        $entity
            ->setRawData($data)
            ->setOid($formatted['oid'])
            ->setDestination($formatted['destination'])
            ->setLanguage($formatted['language'])
            ->setProperty($formatted['property'])
            ->setValue($formatted['value'])
        ;

        return $entity;
    }
}
