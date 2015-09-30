<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\ImportBundle\Reader\Json;

use Application\ImportBundle\Reader\ReaderInterface;

/**
 * Json data parser interface.
 *
 * Interface JsonReaderInterface
 */
interface JsonReaderInterface extends ReaderInterface
{
    const ENTITY_PERSON_PATH                  = 'people/';
    const ENTITY_PERSON_CUSTOM_DEF_PATH       = 'people_custom_def/';
    const ENTITY_TICKET_PATH                  = 'tickets/';
    const ENTITY_TICKET_CUSTOM_DEF_PATH       = 'tickets_custom_def/';
    const ENTITY_ARTICLE_PATH                 = 'articles/';
    const ENTITY_ARTICLE_CATEGORY_PATH        = 'article_categories/';
    const ENTITY_ARTICLE_CUSTOM_DEF_PATH      = 'article_custom_def/';
    const ENTITY_DOWNLOAD_PATH                = 'downloads/';
    const ENTITY_FEEDBACK_PATH                = 'feedback/';
    const ENTITY_FEEDBACK_CUSTOM_DEF_PATH     = 'feedback_custom_def/';
    const ENTITY_NEWS_PATH                    = 'news/';
    const ENTITY_ORGANIZATION_PATH            = 'organizations/';
    const ENTITY_ORGANIZATION_CUSTOM_DEF_PATH = 'organizations_custom_def/';

    /**
     * Returns count of json files in the dir
     * One record per file.
     *
     * @param string $entity_path
     * @param int    $batch_num
     *
     * @return int
     */
    public function getDirectoryFilesCount($entity_path, $batch_num);

    /**
     * Returns directory files data
     * Reads all directory json files, decode and returns  array.
     *
     * @param string $entity_path
     * @param int    $batch_num
     *
     * @return array
     */
    public function getData($entity_path, $batch_num);
}
