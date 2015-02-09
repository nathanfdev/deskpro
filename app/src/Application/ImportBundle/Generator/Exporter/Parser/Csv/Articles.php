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
        return $this->reader->getRowsCount($this->getConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection = new Entity\Collection();
        $articles   = $this->reader->getData($this->getConfig());

        foreach ($articles as $num => $article) {
            $this->advanceProgressBar();

            if ($this->hasRequiredArticleColumns($article) === false) {
                $this->logWarning(sprintf('Invalid article record found (Skipping): %d', $num));
            } else {
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

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
            }
        }

        return $collection;
    }

    /**
     * Check if article has all required columns
     *
     * @param array $article
     * @return bool
     */
    private function hasRequiredArticleColumns(array $article)
    {
        return $this->hasRequiredColumns($article, array(
            'person',
            'title',
            'content',
            'slug',
            'language',
            'status',
            'category',
            'label',
        ));
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
