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

namespace Application\ImportBundle\Generator\Exporter\Parser\Json;

use Application\ImportBundle\Generator\Writer\Json\Destination;
use Application\ImportBundle\Entity;
use DateTime;

/**
 * News json file parser
 *
 * Class News
 * @package Application\ImportBundle\Generator\Exporter\Parser\Json
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
        return $this->reader->getDirectoryFilesCount($this->getConfig());
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
                    ->setOid($news['oid'])
                    ->setPersonEmail($news['person'])
                    ->setLanguage($news['language'])
                    ->setSlug($news['slug'])
                    ->setTitle($news['title'])
                    ->setContent($news['content'])
                    ->setViewCount($news['view_count'])
                    ->setTotalRating($news['total_rating'])
                    ->setNumComments($news['num_comments'])
                    ->setNumRatings($news['num_ratings'])
                    ->setStatus($news['status'])
                    ->setDateCreated($news['date_created'])
                    ->setCategory($news['category']);

                if ($news['date_published']) {
                    $entity->setDatePublished(new DateTime($news['date_published']));
                }
                foreach ($news['labels'] as $label) {
                    $entity->addLabel($label);
                }

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
            }
        }

        return $collection;
    }

    /**
     * Returns record type reader config
     *
     * @return \Application\ImportBundle\Reader\Json\JsonConfig
     */
    private function getConfig()
    {
        return $this->getReaderConfig(Destination\DestinationInterface::ENTITY_NEWS_PATH);
    }

    /**
     * Check if news has all required columns
     *
     * @param array $news
     * @return bool
     */
    private function hasRequiredNewsColumns(array $news)
    {
        $columns = array(
            'oid',
            'person',
            'language',
            'slug',
            'title',
            'content',
            'view_count',
            'total_rating',
            'num_comments',
            'num_ratings',
            'status',
            'date_created',
            'date_published',
            'category',
            'labels',
        );

        return $this->hasRequiredColumns($news, $columns) && is_array($news['labels']);
    }
}
