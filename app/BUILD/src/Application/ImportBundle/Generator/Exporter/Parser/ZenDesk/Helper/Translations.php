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

namespace Application\ImportBundle\Generator\Exporter\Parser\ZenDesk\Helper;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\AbstractParserFormatterHelper;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;
use Application\ImportBundle\Generator\Exporter\Parser\SkippingException;
use Application\ImportBundle\Reader\ZenDesk\LocaleMapper;

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
            ->setPrefix('ZDTranslation')
            ->setRefColumn('id')
            ->setMethod('exportTranslation')
        ;

        return $this->exportCollection($config);
    }

    /**
     * Exports object lang entity.
     *
     * @param array $data
     *
     * @return Entity\ObjectLang[]
     */
    public function exportTranslation(array $data)
    {
        $formatted = $this->formatter->format($data, [
            'id'          => TransformerInterface::TYPE_STRING,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, [
                'prefix' => 'translation_',
                'ref'    => 'id',
            ]),
            'locale' => TransformerInterface::TYPE_STRING,
            'title'  => TransformerInterface::TYPE_STRING,
            'body'   => TransformerInterface::TYPE_STRING,
            'draft'  => TransformerInterface::TYPE_BOOLEAN,
        ]);

        if ($formatted['draft']) {
            throw new SkippingException('Draft translation', $formatted);
        }

        $language = LocaleMapper::getLocale($formatted['locale']);
        if ('?' === $language) {
            throw new SkippingException('Locale is not supported', $formatted);
        }

        $title_entity = new Entity\ObjectLang();
        $title_entity
            ->setRawData($data)
            ->setOid($formatted['id'].'_title')
            ->setDestination($formatted['destination'].'_title')
            ->setLanguage($language)
            ->setProperty('title')
            ->setValue($formatted['title'])
        ;

        $content_entity = new Entity\ObjectLang();
        $content_entity
            ->setRawData($data)
            ->setOid($formatted['id'].'_content')
            ->setDestination($formatted['destination'].'_content')
            ->setLanguage($language)
            ->setProperty('content')
            ->setValue($formatted['body'])
        ;

        $collection = new Entity\Collection();
        $collection
            ->attach($title_entity)
            ->attach($content_entity)
        ;

        return $collection;
    }
}
