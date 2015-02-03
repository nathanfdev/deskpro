<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

use Application\ImportBundle\Exception\BadDataException;
use Application\ImportBundle\Value\KbValue;
use Orb\Validator\StringEmail;

class KbValueImporter extends AbstractValueImporter
{
    /**
     * @param  mixed                                                $kbval
     * @throws \Application\ImportBundle\Exception\BadDataException
     */
    public function importValue($kbval)
    {
//        if (!($kbval instanceof KbValue)) {
//            throw new \InvalidArgumentException("This importer can only import KbValue");
//        }
//
//        if (empty($kbval->title) || empty($kbval->content)) {
//            $this->getLogger()->warning(sprintf("An Article must have both a title and content (skipping)"));
//
//            return false;
//        }
//
//        if ($this->checkIsDuplicate($kbval->title)) {
//            $this->getLogger()->warning(sprintf("An Article with the title \"%s\" already exists (skipping)", $kbval->title));
//
//            return false;
//        }

//        $log_id = "Article :: " . $kbval->oid . " ";
//
//        $record = array();

        $articleId = null;

        #------------------------------
        # Grab the person
        #------------------------------
//        if ($kbval->person && StringEmail::isValueValid($kbval->person)) {
//            $personId = $this->getMappers()->findIdFromMappedValue('person', $kbval->person);
//
//            if ($personId) {
//                $this->getLogger()->info(sprintf("[%s] Found existing person %s", $log_id, $kbval->person));
//                $record['person_id'] = $personId;
//            } else {
//                $this->getLogger()->warning(sprintf("[%s] Unknown person with email %s (skipping)", $log_id, $kbval->person));
//
//                return false;
//            }
//        } else {
//            throw new BadDataException("KbValue must have a person specified");
//        }

        // Lang
//        if ($kbval->language) {
//            $languageId = $this->getMappers()->findIdFromMappedValue('language', $kbval->language);
//            if ($languageId) {
//                $this->getLogger()->info(sprintf("[%s] Found existing language %s", $log_id, $kbval->language));
//                $record['language_id'] = $languageId;
//            } else {
//                $this->getLogger()->notice(sprintf("[%s] Could not map language value: %s", $log_id, $kbval->language));
//            }
//        }

//        $record['title']		= $kbval->title;
//        $record['content']		= $kbval->content;
//        $record['slug']			= $kbval->slug;
//        $record['date_created']		= isset($kbval->date_created) ? $kbval->date_created->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
//        $record['date_published']	= isset($kbval->date_published) ? $kbval->date_published->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
//        $record['date_end']		= isset($kbval->date_end) ? $kbval->date_end->format('Y-m-d H:i:s') : null;
        $record['total_rating']		= $kbval->total_rating;
        $record['num_comments']		= $kbval->num_comments;
        $record['num_ratings']		= $kbval->num_ratings;

//        if ($kbval->date_end) {
//            $record['status'] = 'archived';
//        } elseif ($kbval->date_published) {
//            $record['status'] = 'published';
//        }

        #------------------------------
        # Save data
        #------------------------------

//        if (!$this->isTestMode()) {
//            $this->getDb()->insert('articles', $record);
//
//            $articleId = $this->getDb()->lastInsertId();
//
//            $this->getLogger()->info(sprintf("[%s] Created %d", $log_id, $articleId));
//        }

        #------------------------------
        # Article Categories
        #------------------------------
//        if ($articleId && $kbval->categories) {
//            foreach ($kbval->categories as $category) {
//                $record = array();
//
//                $articleCategoryId = $this->getMappers()->findIdFromMappedValue('article_category', $category);
//
//                if ($articleCategoryId) {
//                    $this->getLogger()->info(sprintf("[%s] Found existing article category %s", $log_id, $category));
//                    $record['category_id'] = $articleCategoryId;
//                } else {
//                    $this->getLogger()->warning(sprintf("[%s] New article category %s (creating)", $log_id, $category));
//
//                    $this->getDb()->insert('article_categories', array('title' => $category));
//
//                    $record['category_id'] = $this->getDb()->lastInsertId();
//                }
//
//                $record['article_id'] = $articleId;
//
//                $this->getDb()->insert('article_to_categories', $record);
//            }
//        }

        #------------------------------
        # Labels
        #------------------------------
//        if ($articleId && $kbval->labels) {
//            $batch = array_map(function ($l) use ($articleId) {
//                return array(
//                    'article_id'	=> $articleId,
//                    'label'		=> $l
//                );
//            }, $kbval->labels);
//
//            $this->getDb()->batchInsert('labels_articles', $batch, true);
//        }
    }

//    private function checkIsDuplicate($title)
//    {
//        $query = 'SELECT id FROM articles WHERE title = ?';
//
//        return $this->getDb()->fetchColumn($query, array($title));
//    }
}
