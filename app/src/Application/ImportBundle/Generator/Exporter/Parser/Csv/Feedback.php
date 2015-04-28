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
use DateTime;

/**
 * Feedback csv file parser
 *
 * Class Feedback
 * @package Application\ImportBundle\Generator\Exporter\Parser\Csv
 */
final class Feedback extends AbstractParser
{
    const FEEDBACK_PREFIX = 'feedback_';

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
        return $this->getReaderCount($this->getFeedbackConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection     = new Entity\Collection();
        $feedback_items = $this->getReaderData($this->getFeedbackConfig());
        $attachments    = $this->exportFeedbackAttachments();

        foreach ($feedback_items as $num => $feedback) {
            $this->advanceProgressBar();

            try {
                $entity = $this->exportFeedback($feedback);
                if ($entity) {
                    foreach ($attachments as $attachment) {
                        /** @var Entity\Attachment $attachment */
                        if ($attachment->getDestination() === self::FEEDBACK_PREFIX . $entity->getOid()) {
                            $entity->addAttachment($attachment);
                        }
                    }

                    $collection->attach($entity);
                    $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
                } else {
                    $this->logWarning(sprintf('Invalid feedback record `%d` found (Skipping)', $num));
                }

            } catch (NoColumnException $e) {
                $this->logWarning(sprintf(
                    'Invalid feedback record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));
            }
        }

        return $collection;
    }

    /**
     * Returns a feedback entity
     *
     * @param array $feedback
     * @return Entity\Feedback|null
     */
    private function exportFeedback(array $feedback)
    {
        if ($this->isFeedbackValid($feedback)) {
            $entity = new Entity\Feedback();
            $entity
                ->setDestination('feedback_' . $feedback['id'])
                ->setOid($feedback['id'])
                ->setPersonEmail($feedback['person'])
                ->setLanguage($feedback['language'])
                ->setTitle($feedback['title'])
                ->setContent($feedback['content'])
                ->setSlug($feedback['slug'])
                ->setPopularity($feedback['popularity'])
                ->setStatus($feedback['status'])
                ->setCategory($feedback['category'])
                ->setDateCreated($this->getFromStringOrCurrentDateTime($feedback['date_created']));

            if ($feedback['date_published']) {
                $entity->setDatePublished(new DateTime($feedback['date_published']));
            }
            if ($feedback['label']) {
                $entity->addLabel($feedback['label']);
            }

            return $entity;
        }

        return null;
    }

    /**
     * Returns a collection of ticket attachments
     *
     * @return Entity\Collection
     */
    private function exportFeedbackAttachments()
    {
        return $this->exportAttachments($this->getFeedbackAttachmentsConfig(), self::FEEDBACK_PREFIX, 'feedback_id');
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
            'id',
            'person',
            'title',
            'content',
            'slug',
            'language',
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
    private function getFeedbackConfig()
    {
        return $this->getReaderConfig(self::FILE_FEEDBACK);
    }

    /**
     * Returns reader config of feedback attachment records
     *
     * @return \Application\ImportBundle\Reader\Csv\CsvConfig
     */
    private function getFeedbackAttachmentsConfig()
    {
        return $this->getReaderConfig(self::FILE_FEEDBACK_ATTACHMENTS);
    }
}
