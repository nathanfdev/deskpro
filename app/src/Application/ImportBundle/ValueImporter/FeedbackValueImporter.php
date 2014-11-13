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

/**
 * @package Importer
 */

namespace Application\ImportBundle\ValueImporter;

use Application\ImportBundle\Value\FeedbackValue;
use Application\ImportBundle\Exception\BadDataException;
use Application\ImportBundle\Exception\DuplicateValueException;
use Orb\Validator\StringEmail;

class FeedbackValueImporter extends AbstractValueImporter
{
    /**
     *
     * @param  \Application\ImportBundle\Value\FeedbackValue $fval
     * @return boolean
     * @throws \InvalidArgumentException
     * @throws BadDataException
     * @throws DuplicateValueException
     */
    public function importValue($fval)
    {
        if (!($fval instanceof FeedbackValue)) {
            throw new \InvalidArgumentException("This importer can only import Feedback Items");
        }

        if (empty($fval->title) || empty($fval->content)) {
            throw new BadDataException(sprintf("A Feedback item must have both a title and content (skipping)"));
        }

        if ($this->checkIsDuplicate($fval->title)) {
            throw new DuplicateValueException(sprintf("A Feedback item with the title \"%s\" already exists (skipping)", $fval->title));
        }

        $log_id = "Feedback Item :: " . $fval->oid . " ";

        $record = array();

        $feedback_id = null;

        #------------------------------
        # Grab the person
        #------------------------------
        if ($fval->person && StringEmail::isValueValid($fval->person)) {
            $personId = $this->getMappers()->findIdFromMappedValue('person', $fval->person);

            if ($personId) {
                $this->getLogger()->info(sprintf("[%s] Found existing person %s", $log_id, $fval->person));
                $record['person_id'] = $personId;
            } else {
                $this->getLogger()->warning(sprintf("[%s] Unknown person with email %s (skipping)", $log_id, $fval->person));

                return false;
            }
        } else {
            throw new BadDataException("Feedback item must have a person specified");
        }

        // Lang
        if ($fval->language) {
            $languageId = $this->getMappers()->findIdFromMappedValue('language', $fval->language);
            if ($languageId) {
                $this->getLogger()->notice(sprintf("[%s] Found existing language %s", $log_id, $fval->language));
                $record['language_id'] = $languageId;
            } else {
                $this->getLogger()->notice(sprintf("[%s] Could not map language value: %s", $log_id, $fval->language));
            }
        }

        #------------------------------
        # Category
        #------------------------------
        if ($fval->category) {
            $category = $fval->category;

            $category_id = $this->getMappers()->findIdFromMappedValue('feedback_category', $category);

            if ($category_id) {
                $this->getLogger()->info(sprintf("[%s] Found existing feedback category %s", $log_id, $category));

                $record['category_id'] = $category_id;
            } else {
                $this->getLogger()->warning(sprintf("[%s] New feedback category %s (creating)", $log_id, $category));

                $this->getDb()->insert('feedback_categories', array('title' => $category));

                $record['category_id'] = $this->getDb()->lastInsertId();

                $this->getMappers()->learnMapping('feedback_category', array('id' => $record['category_id'], 'title' => $category));
            }
        }

        $record['title']		= $fval->title;
        $record['content']		= $fval->content;
        $record['slug']			= $fval->slug;
        $record['date_created']		= isset($fval->date_created) ? $fval->date_created->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
        $record['date_published']	= isset($fval->date_published) ? $fval->date_published->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
        $record['total_rating']		= $fval->total_rating;
        $record['num_comments']		= $fval->num_comments;
        $record['num_ratings']		= $fval->num_ratings;
        $record['view_count']		= $fval->view_count;
        $record['popularity']		= $fval->popularity;

        if ($fval->date_published) {
            $record['status'] = 'published';
        }

        #------------------------------
        # Save data
        #------------------------------

        if (!$this->isTestMode()) {
            $this->getDb()->insert('feedback', $record);

            $feedback_id = $this->getDb()->lastInsertId();

            $this->getLogger()->info(sprintf("[%s] Created %d", $log_id, $feedback_id));
        }

        #------------------------------
        # Labels
        #------------------------------
        if ($feedback_id && $fval->labels) {
            $batch = array_map(function ($l) use ($feedback_id) {
                return array(
                    'feedback_id'	=> $feedback_id,
                    'label'		=> $l
                );
            }, $fval->labels);

            $this->getDb()->batchInsert('labels_feedback', $batch, true);
        }
    }

    private function checkIsDuplicate($title)
    {
        $query = 'SELECT id FROM feedback WHERE title = ?';

        return $this->getDb()->fetchColumn($query, array($title));
    }
}
