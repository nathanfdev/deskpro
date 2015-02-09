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
 * News csv file parser
 *
 * Class News
 * @package Application\ImportBundle\Generator\Exporter\Parser\Csv
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
        return $this->reader->getRowsCount($this->getConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection = new Entity\Collection();
        $news_list  = $this->reader->getData($this->getConfig());

        foreach ($news_list as $num => $news) {
            $this->advanceProgressBar();

            if ($this->hasRequiredNewsColumns($news) === false) {
                $this->logWarning(sprintf('Invalid news record found (Skipping): %d', $num));
            } else {
                $entity = new Entity\News();
                $entity
                    ->setDestination('news_' . $num)
                    ->setOid($num)
                    ->setPersonEmail($news['person'])
                    ->setLanguage($news['language'])
                    ->setSlug($news['slug'])
                    ->setTitle($news['title'])
                    ->setContent($news['content'])
                    ->setSlug($news['slug'])
                    ->setStatus($news['status'])
                    ->setDateCreated($this->getFromStringOrCurrentDateTime($news['date_created']))
                    ->setDatePublished($this->getFromStringOrCurrentDateTime($news['date_published']))
                    ->setCategory($news['category'])
                    ->addLabel($news['label']);

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
            }
        }

        return $collection;
    }

    /**
     * Check if news has all required columns
     *
     * @param array $news
     * @return bool
     */
    private function hasRequiredNewsColumns(array $news)
    {
        return $this->hasRequiredColumns($news, array(
            'oid',
            'person',
            'language',
            'title',
            'content',
            'status',
            'date_created',
            'date_published',
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
        return $this->getReaderConfig(self::FILE_NEWS);
    }
}
