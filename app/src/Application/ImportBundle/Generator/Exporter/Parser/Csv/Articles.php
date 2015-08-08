<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Generator\Exporter\Parser\Csv;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Helper\ColumnHelper;
use Application\ImportBundle\Generator\Exporter\Formatter\DateFormatter;
use Application\ImportBundle\Generator\Exporter\Formatter\DestinationFormatter;
use Application\ImportBundle\Generator\Exporter\Parser\NoColumnException;

/**
 * Articles csv file parser
 *
 * Class Articles
 * @package Application\ImportBundle\Generator\Exporter\Parser\Csv
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
        return $this->getReaderCount($this->getArticleReaderConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection    = new Entity\Collection();

        $articles      = $this->getReaderData($this->getArticleReaderConfig());
        $custom_fields = $this->exportArticleCustomFields();

        foreach ($articles as $num => $article) {
            $this->advanceProgressBar();

            try {
                $entity = $this->exportArticle($num, $article);
                if ($entity) {
                    foreach ($custom_fields as $custom_field_entity) {
                        /** @var Entity\CustomField $custom_field_entity */
                        if ($entity->getDestination() === $custom_field_entity->getDestination()) {
                            $entity->addCustomField($custom_field_entity);
                        }
                    }

                    $inline_custom_fields = $this->exportInlineCustomFields($entity->getDestination(), $article);
                    foreach ($inline_custom_fields as $custom_field_entity) {
                        $entity->addCustomField($custom_field_entity);
                    }

                    $collection->attach($entity);
                    $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
                } else {
                    $this->logWarning(sprintf('Invalid article record `%d` found (Skipping)', $num));
                }

            } catch (NoColumnException $e) {
                $this->logWarning(sprintf(
                    'Invalid article record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));
            }
        }

        return $collection;
    }

    /**
     * Returns an article entity
     *
     * @param int   $num
     * @param array $article
     *
     * @return Entity\Article|null
     */
    private function exportArticle($num, array $article)
    {
        if ($this->isArticleValid($article)) {
            $article_id = isset($article['id']) ? $article['id'] : 'num_' . $num;
            $entity     = new Entity\Article();
            $entity
                ->setRawData($article)
                ->setDestination(DestinationFormatter::formatDestination(self::ARTICLE_PREFIX, $article_id))
                ->setOid($article_id)
                ->setPersonEmail($article['person'])
                ->setTitle($article['title'])
                ->setContent($article['content'])
                ->setSlug($article['slug'])
                ->setLanguage($article['language'])
                ->setDateCreated(DateFormatter::getFromStringOrCurrentDateTime($article['date_created'], $this->logger))
                ->setStatus($article['status'])
            ;

            if ($article['label']) {
                $entity->addLabel($article['label']);
            }
            if ($article['category']) {
                $entity->addCategory($article['category']);
            }

            return $entity;
        }

        return null;
    }

    /**
     * Returns a collection of articles custom field data
     *
     * @return Entity\Collection
     */
    private function exportArticleCustomFields()
    {
        return $this->exportCustomFields($this->getArticleCustomFieldReaderConfig(), self::ARTICLE_PREFIX, 'article_id');
    }

    /**
     * Check if article has all required columns
     *
     * @param array $article
     * @return bool
     */
    private function isArticleValid(array $article)
    {
        $columns = array(
            'person',
            'title',
            'content',
            'slug',
            'language',
            'status',
            'category',
            'label',
            'date_created',
        );

        return ColumnHelper::hasRequiredColumns($article, $columns);
    }

    /**
     * Returns record type reader config
     *
     * @return \Application\ImportBundle\Reader\Csv\CsvConfig
     */
    private function getArticleReaderConfig()
    {
        return $this->getReaderConfig(self::FILE_ARTICLES);
    }

    /**
     * Returns reader config for articles custom field records
     *
     * @return \Application\ImportBundle\Reader\Csv\CsvConfig
     */
    private function getArticleCustomFieldReaderConfig()
    {
        return $this->getReaderConfig(self::FILE_ARTICLE_CUSTOM_FIELDS);
    }
}
