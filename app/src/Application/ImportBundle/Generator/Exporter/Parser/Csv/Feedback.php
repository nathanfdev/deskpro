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
 * Feedback csv file parser
 *
 * Class Feedback
 * @package Application\ImportBundle\Generator\Exporter\Parser\Csv
 */
final class Feedback extends AbstractParser
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_FEEDBACK;
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
        $collection     = new Entity\Collection();
        $feedback_items = $this->getReaderData($this->getConfig());

        foreach ($feedback_items as $num => $feedback) {
            $this->advanceProgressBar();

            try {
                $entity = $this->exportFeedback($num, $feedback);
                if ($entity) {
                    $collection->attach($entity);
                    $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
                } else {
                    $this->logError(sprintf('Invalid feedback record `%d` found (Skipping)', $num));
                }

            } catch (NoColumnException $e) {
                $this->logError(sprintf(
                    'Invalid feedback record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));
            }
        }

        return $collection;
    }

    /**
     * @param int   $num
     * @param array $feedback
     *
     * @return Entity\Feedback|null
     */
    private function exportFeedback($num, array $feedback)
    {
        if ($this->isFeedbackValid($feedback)) {
            $entity = new Entity\Feedback();
            $entity
                ->setDestination('feedback_' . $num)
                ->setOid($num)
                ->setPersonEmail($feedback['person'])
                ->setLanguage($feedback['language'])
                ->setTitle($feedback['title'])
                ->setContent($feedback['content'])
                ->setSlug($feedback['slug'])
                ->setPopularity($feedback['popularity'])
                ->setStatus($feedback['status'])
                ->setCategory($feedback['category'])
                ->setDateCreated($this->getFromStringOrCurrentDateTime($feedback['date_created']))
                ->setDatePublished($this->getFromStringOrCurrentDateTime($feedback['date_published']));

            return $entity;
        }

        return null;
    }

    /**
     * Check if feedback has all required columns
     *
     * @param array $feedback
     * @return bool
     */
    private function isFeedbackValid(array $feedback)
    {
        $columns = array(
            'person',
            'language',
            'title',
            'content',
            'slug',
            'popularity',
            'status',
            'category',
            'label',
            'date_created',
            'date_published',
        );

        return $this->hasRequiredColumns($feedback, $columns);
    }

    /**
     * Returns record type reader config
     *
     * @return \Application\ImportBundle\Reader\Csv\CsvConfig
     */
    private function getConfig()
    {
        return $this->getReaderConfig(self::FILE_FEEDBACK);
    }
}
