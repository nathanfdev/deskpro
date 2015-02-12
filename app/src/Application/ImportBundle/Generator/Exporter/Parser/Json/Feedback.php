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

use Application\ImportBundle\Generator\Exporter\Parser\NoColumnException;
use Application\ImportBundle\Generator\Exporter\Parser\NotArrayException;
use Application\ImportBundle\Generator\Writer\Json\Destination;
use Application\ImportBundle\Entity;
use Orb\Util\Strings;
use DateTime;

/**
 * Feedback json file parser
 *
 * Class Feedback
 * @package Application\ImportBundle\Generator\Exporter\Parser\Json
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
        return $this->reader->getDirectoryFilesCount($this->getConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection     = new Entity\Collection();
        $feedback_items = $this->reader->getData($this->getConfig());

        foreach ($feedback_items as $num => $feedback) {
            $this->advanceProgressBar();

            try {
                $entity = $this->exportFeedback($feedback);
                if ($entity) {
                    $collection->attach($entity);
                    $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
                } else {
                    $this->logWarning(sprintf('Invalid feedback record found (Skipping): %d', $num));
                }

            } catch (NoColumnException $e) {
                $this->logWarning(sprintf(
                    'Invalid feedback record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));

            } catch (NotArrayException $e) {
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
     * @return Entity\Feedback
     */
    private function exportFeedback(array $feedback)
    {
        if ($this->isFeedbackValid($feedback)) {
            $entity = new Entity\Feedback();
            $entity
                ->setDestination('feedback_' . $feedback['oid'])
                ->setOid($feedback['oid'])
                ->setPersonEmail($feedback['person'])
                ->setLanguage($feedback['language'])
                ->setTitle($feedback['title'])
                ->setContent($feedback['content'])
                ->setSlug($feedback['slug'])
                ->setPopularity($feedback['popularity'])
                ->setStatus($feedback['status'])
                ->setTotalRating($feedback['total_rating'])
                ->setNumComments($feedback['num_comments'])
                ->setNumRatings($feedback['num_ratings'])
                ->setViewCount($feedback['view_count'])
                ->setCategory($feedback['category'])
                ->setDateCreated(new DateTime($feedback['date_created']));

            if ($feedback['date_published']) {
                $entity->setDatePublished(new DateTime($feedback['date_published']));
            }
            foreach ($feedback['labels'] as $label) {
                $entity->addLabel($label);
            }

            return $entity;
        }

        return null;
    }

    /**
     * Returns record type reader config
     *
     * @return \Application\ImportBundle\Reader\Json\JsonConfig
     */
    private function getConfig()
    {
        return $this->getReaderConfig(Destination\DestinationInterface::ENTITY_FEEDBACK_PATH);
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
            'oid',
            'person',
            'language',
            'title',
            'content',
            'popularity',
            'status',
            'total_rating',
            'num_comments',
            'num_ratings',
            'view_count',
            'category',
            'labels',
            'date_created',
            'date_published',
        );

        return $this->hasRequiredColumns($feedback, $columns)
            && $this->isArrayColumn($feedback, 'labels');
    }
}
