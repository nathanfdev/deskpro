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

namespace Application\ImportBundle\Generator\Exporter\Parser\Csv;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;
use Application\ImportBundle\Reader\Csv\CsvReaderInterface;

/**
 * Article categories csv file parser.
 *
 * Class ArticleCategories
 */
final class ArticleCategories extends AbstractParser
{
    /**
     * @var int
     */
    private $auto_generate_num = 0;

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
        return $this->getReaderCount(CsvReaderInterface::FILE_ARTICLE_CATEGORIES);
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($this->getReaderData(CsvReaderInterface::FILE_ARTICLE_CATEGORIES))
            ->setPrefix('CSVArticleCategory')
            ->setRefColumn('id')
            ->setMethod('exportCategory')
            ->setAdvanceProgressbar(true)
        ;

        $collection              = $this->exportCollection($config);
        $this->auto_generate_num = (int) $collection->getMaxOid() + 1;

        return $this->toDeepCollection($collection);
    }

    /**
     * Converts to category tree collection.
     *
     * @param Entity\Collection      $list_collection
     * @param Entity\Collection|null $deep_collection
     * @param int                    $deep_level
     *
     * @return Entity\Collection
     */
    private function toDeepCollection(Entity\Collection $list_collection, Entity\Collection $deep_collection = null, $deep_level = 1)
    {
        $deep_collection = $deep_collection ?: new Entity\Collection();

        foreach ($list_collection as $category) {
            /* @var Entity\ArticleCategory $category */
            $category_path = explode('>', $category->getTitle());
            $category_path = array_map('trim', $category_path);

            if (count($category_path) === $deep_level) {
                $category->setTitle(end($category_path));
                $list_collection->detach($category);

                if ($deep_level > 1) {
                    $parent_category = $this->findOrCreateDeepParentCategory($deep_collection, $category_path);
                    $parent_category->addCategory($category);
                } else {
                    $deep_collection->attach($category);
                }
            }
        }

        if ($list_collection->count() > 0) {
            return $this->toDeepCollection($list_collection, $deep_collection, $deep_level + 1);
        }

        return $deep_collection;
    }

    /**
     * Returns parent article category entity
     * Creates a new entity if not found.
     *
     * @param Entity\Collection $collection
     * @param array             $category_path
     *
     * @return Entity\ArticleCategory|null
     */
    private function findOrCreateDeepParentCategory(Entity\Collection $collection, array $category_path)
    {
        $title    = array_shift($category_path);
        $category = null;

        foreach ($collection as $exist_category) {
            /* @var Entity\ArticleCategory $category */
            if ($exist_category->getTitle() === $title) {
                $category = $exist_category;
                break;
            }
        }

        if (null === $category) {
            $category = new Entity\ArticleCategory();
            $category
                ->setOid($this->auto_generate_num)
                ->setDestination('article_category_'.$this->auto_generate_num)
                ->setRawData(array(
                    'title'          => $title,
                    'auto_generated' => true,
                ))
                ->setTitle($title)
                ->setAsAgent(false)
                ->setAsBook(false)
            ;

            $collection->attach($category);
            ++$this->auto_generate_num;
        }

        if (count($category_path) > 1) {
            return $this->findOrCreateDeepParentCategory($category->getCategories(), $category_path);
        }

        return $category;
    }

    /**
     * Returns an article category entity.
     *
     * @param array $data
     * @param int   $num
     *
     * @return Entity\ArticleCategory
     */
    protected function exportCategory(array $data, $num)
    {
        $formatted = $this->formatter->format($data, array(
            'id'          => TransformerConfiguration::create(TransformerInterface::TYPE_STRING, array(
                'default' => 'num_'.$num,
            )),
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix'  => 'article_category_',
                'ref'     => 'id',
            )),
            'title'    => TransformerInterface::TYPE_STRING,
            'is_book'  => TransformerInterface::TYPE_BOOLEAN,
            'is_agent' => TransformerInterface::TYPE_BOOLEAN,
        ));

        $entity = new Entity\ArticleCategory();
        $entity
            ->setRawData($data)
            ->setOid($formatted['id'])
            ->setDestination($formatted['destination'])
            ->setTitle($formatted['title'])
            ->setAsAgent($formatted['is_agent'])
            ->setAsBook($formatted['is_book'])
        ;

        return $entity;
    }
}
