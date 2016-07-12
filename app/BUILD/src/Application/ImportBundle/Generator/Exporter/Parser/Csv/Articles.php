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

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;
use Application\ImportBundle\Reader\Csv\CsvReaderInterface;

/**
 * Articles csv file parser.
 *
 * Class Articles
 */
final class Articles extends AbstractParser
{
    const ARTICLE_PREFIX = 'article_';

    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_ARTICLE;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return $this->getReaderCount(CsvReaderInterface::FILE_ARTICLES);
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($this->getReaderData(CsvReaderInterface::FILE_ARTICLES))
            ->setPrefix('CSVArticle')
            ->setRefColumn('id')
            ->setMethod('exportArticle')
            ->setAdvanceProgressbar(true)
        ;

        $collection    = $this->exportCollection($config);
        $custom_fields = $this->exportArticleCustomFields();

        foreach ($collection as $article) {
            /* @var Entity\Article $article */
            foreach ($custom_fields as $custom_field_entity) {
                if ($article->getDestination() === $custom_field_entity->getDestination()) {
                    $article->addCustomField($custom_field_entity);
                }
            }

            $inline_custom_fields = $this->getInlineCustomFieldsParser()->export($article->getDestination(), $article->getRawData());
            foreach ($inline_custom_fields as $custom_field_entity) {
                $article->addCustomField($custom_field_entity);
            }
        }

        return $collection;
    }

    /**
     * Returns an article entity.
     *
     * @param array $data
     * @param int   $num
     *
     * @return Entity\Article|null
     */
    protected function exportArticle(array $data, $num)
    {
        $formatted = $this->formatter->format($data, [
            'id' => TransformerConfiguration::create(TransformerInterface::TYPE_STRING, [
                'default' => 'num_'.$num,
            ]),
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, [
                'prefix' => self::ARTICLE_PREFIX,
                'ref'    => 'id',
            ]),
            'person'       => TransformerInterface::TYPE_STRING,
            'title'        => TransformerInterface::TYPE_STRING,
            'content'      => TransformerInterface::TYPE_STRING,
            'language'     => TransformerInterface::TYPE_STRING,
            'status'       => TransformerInterface::TYPE_STRING,
            'category'     => TransformerInterface::TYPE_STRING,
            'label'        => TransformerInterface::TYPE_STRING,
            'date_created' => TransformerInterface::TYPE_DATE,
        ]);

        $entity = new Entity\Article();
        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($formatted['id'])
            ->setPersonEmail($formatted['person'])
            ->setTitle($formatted['title'])
            ->setContent($formatted['content'])
            ->setLanguage($formatted['language'])
            ->setDateCreated($formatted['date_created'])
            ->setStatus($formatted['status'])
        ;

        // Set import key if it's real oid only
        if (strpos($entity->getOid(), 'num_') !== 0) {
            $entity->setImportMapKey(DeskPROEntity\ImportMap::TYPE_CSV_ARTICLE);
        }

        if ($formatted['label']) {
            $entity->addLabel($formatted['label']);
        }
        if ($formatted['category']) {
            $entity->addCategory($formatted['category']);
        }

        return $entity;
    }

    /**
     * Returns a collection of articles custom field data.
     *
     * @return Entity\CustomField[]|Entity\Collection
     */
    private function exportArticleCustomFields()
    {
        $data = $this->getReaderData(CsvReaderInterface::FILE_ARTICLE_CUSTOM_FIELDS);

        return $this->getMultipleCustomFieldsParser()->export($data, self::ARTICLE_PREFIX, 'article_id');
    }
}
