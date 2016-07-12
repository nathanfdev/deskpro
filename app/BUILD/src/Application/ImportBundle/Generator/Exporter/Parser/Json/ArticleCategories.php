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
 * Article categories json file parser.
 *
 * Class ArticleCategories
 */
final class ArticleCategories extends AbstractParser
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_ARTICLE_CATEGORY;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return $this->reader->getDirectoryFilesCount(
            JsonReaderInterface::ENTITY_ARTICLE_CATEGORY_PATH,
            $this->getBatchNum()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $data = $this->reader->getData(JsonReaderInterface::ENTITY_ARTICLE_CATEGORY_PATH, $this->getBatchNum());

        return $this->exportCategories($data, true);
    }

    /**
     * Returns a collection of article category entities.
     *
     * @param array $categories
     * @param bool  $advance_progressbar
     *
     * @return Entity\Collection
     */
    protected function exportCategories(array $categories, $advance_progressbar)
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($categories)
            ->setPrefix('JSONArticleCategory')
            ->setRefColumn('oid')
            ->setMethod('exportArticleCategory')
            ->setAdvanceProgressbar($advance_progressbar)
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns an article category entity.
     *
     * @param array $data
     *
     * @return Entity\ArticleCategory
     */
    protected function exportArticleCategory(array $data)
    {
        $formatted = $this->formatter->format($data, [
            'oid'            => TransformerInterface::TYPE_STRING,
            'import_map_key' => TransformerInterface::TYPE_STRING,
            'destination'    => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, [
                'prefix' => 'article_category_',
                'ref'    => 'oid',
            ]),
            'is_agent'    => TransformerInterface::TYPE_BOOLEAN,
            'is_book'     => TransformerInterface::TYPE_BOOLEAN,
            'user_groups' => TransformerInterface::TYPE_ARRAY,
            'categories'  => TransformerInterface::TYPE_ARRAY,
        ]);

        $entity = new Entity\ArticleCategory();
        $entity
            ->setRawData($data)
            ->setImportMapKey($formatted['import_map_key'])
            ->setOid($formatted['oid'])
            ->setDestination($formatted['destination'])
            ->setTitle($formatted['title'])
            ->setAsAgent($formatted['is_agent'])
            ->setAsBook($formatted['is_book'])
            ->setCategories($this->exportCategories($formatted['categories'], false))
        ;

        foreach ($formatted['user_groups'] as $user_group) {
            $entity->addUserGroup($user_group);
        }

        return $entity;
    }
}
