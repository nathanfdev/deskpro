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
use Application\ImportBundle\Generator\Exporter\Parser\NoColumnException;

/**
 * Articles csv file parser
 *
 * Class Articles
 * @package Application\ImportBundle\Generator\Exporter\Parser\Csv
 */
final class Articles extends AbstractParser
{
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
        return $this->getReaderCount($this->getConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection = new Entity\Collection();
        $articles   = $this->getReaderData($this->getConfig());

        foreach ($articles as $num => $article) {
            $this->advanceProgressBar();

            try {
                $entity = $this->exportArticle($num, $article);
                if ($entity) {
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
            $entity = new Entity\Article();
            $entity
                ->setDestination('article_' . $num)
                ->setOid($num)
                ->setPersonEmail($article['person'])
                ->setTitle($article['title'])
                ->setContent($article['content'])
                ->setSlug($article['slug'])
                ->setLanguage($article['language'])
                ->setDateCreated($this->getFromStringOrCurrentDateTime($article['date_created']))
                ->setStatus($article['status'])
                ->addCategory($article['category'])
                ->addLabel($article['label']);

            return $entity;
        }

        return null;
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

        return $this->hasRequiredColumns($article, $columns);
    }

    /**
     * Returns record type reader config
     *
     * @return \Application\ImportBundle\Reader\Csv\CsvConfig
     */
    private function getConfig()
    {
        return $this->getReaderConfig(self::FILE_ARTICLES);
    }
}
