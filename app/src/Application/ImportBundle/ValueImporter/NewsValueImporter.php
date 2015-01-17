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

namespace Application\ImportBundle\ValueImporter;

use Application\ImportBundle\Value\NewsValue;
use Application\ImportBundle\Exception\BadDataException;
use Application\ImportBundle\Exception\DuplicateValueException;
use Orb\Validator\StringEmail;

/**
 * Class NewsValueImporter
 * @package Application\ImportBundle\ValueImporter
 */
class NewsValueImporter extends AbstractValueImporter
{
    /**
     * @param mixed $nval
     *
     * @throws BadDataException
     * @throws DuplicateValueException
     */
    public function importValue($nval)
    {
        if (!($nval instanceof NewsValue)) {
            throw new \InvalidArgumentException("This importer can only import News Items");
        }

        if (empty($nval->title) || empty($nval->content)) {
            throw new BadDataException(sprintf("A News item must have both a title and content (skipping)"));
        }

        if ($this->checkIsDuplicate($nval->title)) {
            throw new DuplicateValueException(sprintf("A News item with the title \"%s\" already exists (skipping)", $nval->title));
        }

        $log_id  = "News Item :: " . $nval->oid . " ";
        $record  = array();
        $news_id = null;

        #------------------------------
        # Grab the person
        #------------------------------
        if ($nval->person && StringEmail::isValueValid($nval->person)) {
            $personId = $this->getMappers()->findIdFromMappedValue('person', $nval->person);

            if ($personId) {
                $this->getLogger()->info(sprintf("[%s] Found existing person %s", $log_id, $nval->person));
                $record['person_id'] = $personId;
            } else {
                $this->getLogger()->warning(sprintf("[%s] Unknown person with email %s (skipping)", $log_id, $nval->person));

                return false;
            }
        } else {
            throw new BadDataException("KbValue must have a person specified");
        }

        // Lang
        if ($nval->language) {
            $languageId = $this->getMappers()->findIdFromMappedValue('language', $nval->language);
            if ($languageId) {
                $this->getLogger()->info(sprintf("[%s] Found existing language %s", $log_id, $nval->language));
                $record['language_id'] = $languageId;
            } else {
                $this->getLogger()->notice(sprintf("[%s] Could not map language value: %s", $log_id, $nval->language));
            }
        }

        #------------------------------
        # Category
        #------------------------------
        if ($nval->category) {
            $category    = $nval->category;
            $category_id = $this->getMappers()->findIdFromMappedValue('news_category', $category);

            if ($category_id) {
                $this->getLogger()->info(sprintf("[%s] Found existing news category %s", $log_id, $category));
                $record['category_id'] = $category_id;
            } else {
                $this->getLogger()->warning(sprintf("[%s] New news category %s (creating)", $log_id, $category));
                $this->getDb()->insert('news_categories', array('title' => $category));

                $record['category_id'] = $this->getDb()->lastInsertId();
                $this->getMappers()->learnMapping('news_category', array('id' => $record['category_id'], 'title' => $category));
            }
        }

        $record = array_merge($record, array(
            'title'          => $nval->title,
            'content'        => $nval->content,
            'slug'           => $nval->slug,
            'date_created'   => isset($nval->date_created) ? $nval->date_created->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'),
            'date_published' => isset($nval->date_published) ? $nval->date_published->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'),
            'total_rating'   => $nval->total_rating,
            'num_comments'   => $nval->num_comments,
            'num_ratings'    => $nval->num_ratings,
            'view_count'     => $nval->view_count
        ));

        if ($nval->date_published) {
            $record['status'] = 'published';
        }

        #------------------------------
        # Save data
        #------------------------------

        if (!$this->isTestMode()) {
            $this->getDb()->insert('news', $record);

            $news_id = $this->getDb()->lastInsertId();

            $this->getLogger()->info(sprintf("[%s] Created %d", $log_id, $news_id));
        }

        #------------------------------
        # Labels
        #------------------------------
        if ($news_id && $nval->labels) {
            $batch = array_map(function ($l) use ($news_id) {
                return array(
                    'news_id'	=> $news_id,
                    'label'		=> $l
                );
            }, $nval->labels);

            $this->getDb()->batchInsert('labels_news', $batch, true);
        }
    }

    private function checkIsDuplicate($title)
    {
        $query = 'SELECT id FROM news WHERE title = ?';

        return $this->getDb()->fetchColumn($query, array($title));
    }
}
