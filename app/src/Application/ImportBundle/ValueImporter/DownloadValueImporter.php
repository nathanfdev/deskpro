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

use Application\ImportBundle\Value\DownloadValue;
use Application\ImportBundle\Exception\BadDataException;
use Application\ImportBundle\Exception\DuplicateValueException;
use Orb\Validator\StringEmail;

class DownloadValueImporter extends AbstractValueImporter
{
    /**
     *
     * @param  \Application\ImportBundle\Value\DownloadValue $dval
     * @return boolean
     * @throws \InvalidArgumentException
     * @throws BadDataException
     * @throws DuplicateValueException
     */
    public function importValue($dval)
    {
        if (!($dval instanceof DownloadValue)) {
            throw new \InvalidArgumentException("This importer can only import Downloads");
        }

        if (empty($dval->title) || empty($dval->content)) {
            throw new BadDataException(sprintf("A Download must have a title and content (skipping)"));
        }

        if (empty($dval->attachment)) {
            throw new BadDataException(sprintf("A Download must have an attachment (skipping)"));
        }

        if ($this->checkIsDuplicate($dval->title)) {
            throw new DuplicateValueException(sprintf("A Download with the title \"%s\" already exists (skipping)", $dval->title));
        }

        $log_id = "Download :: " . $dval->oid . " ";

        $record = array();

        $download_id = null;

        #------------------------------
        # Process the file
        #------------------------------
        if ($this->isTestMode()) {
            $record['blob_id'] = -1;
        } else {
            $attachment = $dval->attachment;

            $attachment_value_importer = new AttachmentValueImporter(
                $this->getMode(),
                $this->getContainer(),
                $this->getLogger(),
                $this->getMappers()
            );

            $blob = $attachment_value_importer->importValue($attachment);
            $record['blob_id'] = $blob['id'];
        }

        #------------------------------
        # Grab the person
        #------------------------------
        if ($dval->person && StringEmail::isValueValid($dval->person)) {
            $personId = $this->getMappers()->findIdFromMappedValue('person', $dval->person);

            if ($personId) {
                $this->getLogger()->info(sprintf("[%s] Found existing person %s", $log_id, $dval->person));
                $record['person_id'] = $personId;
            } else {
                $this->getLogger()->warning(sprintf("[%s] Unknown person with email %s (skipping)", $log_id, $dval->person));

                return false;
            }
        } else {
            throw new BadDataException("Download must have a person specified");
        }

        // Lang
        if ($dval->language) {
            $languageId = $this->getMappers()->findIdFromMappedValue('language', $dval->language);
            if ($languageId) {
                $this->getLogger()->notice(sprintf("[%s] Found existing language %s", $log_id, $dval->language));
                $record['language_id'] = $languageId;
            } else {
                $this->getLogger()->notice(sprintf("[%s] Could not map language value: %s", $log_id, $dval->language));
            }
        }

        #------------------------------
        # Category
        #------------------------------
        if ($dval->category) {
            $category = $dval->category;

            $category_id = $this->getMappers()->findIdFromMappedValue('download_category', $category);

            if ($category_id) {
                $this->getLogger()->info(sprintf("[%s] Found existing download category %s", $log_id, $category));

                $record['category_id'] = $category_id;
            } elseif(!$this->isTestMode()) {
                $this->getLogger()->warning(sprintf("[%s] New download category %s (creating)", $log_id, $category));

                $this->getDb()->insert('download_categories', array('title' => $category));

                $record['category_id'] = $this->getDb()->lastInsertId();

                $this->getMappers()->learnMapping('download_category', array('id' => $record['category_id'], 'title' => $category));
            } else {
                $record['category_id'] = -1;
            }
        }

        $record['title']		= $dval->title;
        $record['content']		= $dval->content;
        $record['slug']			= $dval->slug;
        $record['date_created']		= isset($dval->date_created) ? $dval->date_created->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
        $record['date_published']	= isset($dval->date_published) ? $dval->date_published->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
        $record['total_rating']		= $dval->total_rating;
        $record['num_comments']		= $dval->num_comments;
        $record['num_ratings']		= $dval->num_ratings;
        $record['view_count']		= $dval->view_count;
        $record['num_downloads']	= $dval->num_downloads;

        if ($dval->date_published) {
            $record['status'] = 'published';
        }

        #------------------------------
        # Save data
        #------------------------------

        if (!$this->isTestMode()) {
            $this->getDb()->insert('downloads', $record);

            $download_id = $this->getDb()->lastInsertId();

            $this->getLogger()->info(sprintf("[%s] Created %d", $log_id, $download_id));
        }

        #------------------------------
        # Labels
        #------------------------------
        if ($download_id && $dval->labels) {
            $batch = array_map(function ($l) use ($download_id) {
                return array(
                    'download_id'	=> $download_id,
                    'label'		=> $l
                );
            }, $dval->labels);

            $this->getDb()->batchInsert('labels_downloads', $batch, true);
        }
    }

    private function checkIsDuplicate($title)
    {
        $query = 'SELECT id FROM downloads WHERE title = ?';

        return $this->getDb()->fetchColumn($query, array($title));
    }
}
