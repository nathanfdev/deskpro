<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\ImportBundle\Reader\Csv;

use Application\ImportBundle\Reader\ReaderInterface;

/**
 * Csv data parser interface.
 *
 * Interface CsvReaderInterface
 */
interface CsvReaderInterface extends ReaderInterface
{
    const FILE_ARTICLES                   = 'articles.csv';
    const FILE_ARTICLE_CATEGORIES         = 'article_categories.csv';
    const FILE_ARTICLE_CUSTOM_FIELDS      = 'article_custom_fields.csv';
    const FILE_DOWNLOADS                  = 'downloads.csv';
    const FILE_DOWNLOAD_ATTACHMENTS       = 'downloads_attachments.csv';
    const FILE_FEEDBACK                   = 'feedback.csv';
    const FILE_FEEDBACK_ATTACHMENTS       = 'feedback_attachments.csv';
    const FILE_FEEDBACK_CUSTOM_FIELDS     = 'feedback_custom_fields.csv';
    const FILE_NEWS                       = 'news.csv';
    const FILE_PEOPLE                     = 'people.csv';
    const FILE_PEOPLE_CONTACT_DATA        = 'people_contact_data.csv';
    const FILE_PEOPLE_CUSTOM_FIELDS       = 'people_custom_fields.csv';
    const FILE_PEOPLE_CUSTOM_DEF          = 'people_custom_def.csv';
    const FILE_TICKETS                    = 'tickets.csv';
    const FILE_TICKET_MESSAGES            = 'ticket_messages.csv';
    const FILE_TICKET_ATTACHMENTS         = 'ticket_attachments.csv';
    const FILE_TICKET_CUSTOM_FIELDS       = 'ticket_custom_fields.csv';
    const FILE_TICKET_CUSTOM_DEF          = 'ticket_custom_def.csv';
    const FILE_ORGANIZATIONS              = 'organizations.csv';
    const FILE_ORGANIZATION_CUSTOM_DEF    = 'organization_custom_def.csv';
    const FILE_ORGANIZATION_CONTACT_DATA  = 'organization_contact_data.csv';
    const FILE_ORGANIZATION_CUSTOM_FIELDS = 'organization_custom_fields.csv';

    /**
     * Returns rows count of csv file.
     *
     * @param string $entity_type
     *
     * @return int
     */
    public function getRowsCount($entity_type);

    /**
     * Parse csv file into raw array.
     *
     * @param string $entity_file
     *
     * @return array
     */
    public function getData($entity_file);
}
