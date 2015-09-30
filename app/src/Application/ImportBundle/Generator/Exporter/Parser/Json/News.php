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
use Application\ImportBundle\Reader\Json\JsonReaderInterface;

/**
 * News json file parser.
 *
 * Class News
 */
final class News extends AbstractParser
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_NEWS;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return $this->reader->getDirectoryFilesCount(JsonReaderInterface::ENTITY_NEWS_PATH, $this->getBatchNum());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($this->reader->getData(JsonReaderInterface::ENTITY_NEWS_PATH, $this->getBatchNum()))
            ->setPrefix('JSONNews')
            ->setRefColumn('oid')
            ->setMethod('exportNews')
            ->setAdvanceProgressbar(true)
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns a news entity.
     *
     * @param array $data
     *
     * @return Entity\News
     */
    protected function exportNews(array $data)
    {
        $formatted = $this->formatter->format($data, array(
            'oid'            => TransformerInterface::TYPE_STRING,
            'import_map_key' => TransformerInterface::TYPE_STRING,
            'destination'    => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix'     => 'news_',
                'ref'        => 'oid',
            )),
            'person'         => TransformerInterface::TYPE_STRING,
            'language'       => TransformerInterface::TYPE_STRING,
            'title'          => TransformerInterface::TYPE_STRING,
            'content'        => TransformerInterface::TYPE_STRING,
            'view_count'     => TransformerInterface::TYPE_INT,
            'total_rating'   => TransformerInterface::TYPE_INT,
            'num_comments'   => TransformerInterface::TYPE_INT,
            'num_ratings'    => TransformerInterface::TYPE_INT,
            'status'         => TransformerInterface::TYPE_STRING,
            'date_created'   => TransformerInterface::TYPE_DATE,
            'date_published' => TransformerConfiguration::create(TransformerInterface::TYPE_DATE, array(
                'null'       => true,
            )),
            'category' => TransformerInterface::TYPE_STRING,
            'labels'   => TransformerInterface::TYPE_ARRAY,
        ));

            $entity = new Entity\News();
            $entity
            ->setRawData($data)
            ->setOid($formatted['oid'])
            ->setImportMapKey($formatted['import_map_key'])
            ->setDestination($formatted['destination'])
            ->setPersonEmail($formatted['person'])
            ->setLanguage($formatted['language'])
            ->setSlug($formatted['slug'])
            ->setTitle($formatted['title'])
            ->setContent($formatted['content'])
            ->setSlug($formatted['slug'])
            ->setViewCount($formatted['view_count'])
            ->setTotalRating($formatted['total_rating'])
            ->setNumComments($formatted['num_comments'])
            ->setNumRatings($formatted['num_ratings'])
            ->setStatus($formatted['status'])
            ->setDateCreated($formatted['date_created'])
            ->setCategory($formatted['category'])
            ->setDatePublished($formatted['date_published'])
        ;

        foreach ($formatted['labels'] as $label) {
                $entity->addLabel($label);
            }

            return $entity;
        }
}
