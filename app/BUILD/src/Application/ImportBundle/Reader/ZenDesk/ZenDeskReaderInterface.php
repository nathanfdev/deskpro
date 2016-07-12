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

namespace Application\ImportBundle\Reader\ZenDesk;

use Application\ImportBundle\Reader\ReaderInterface;
use DateTime;

/**
 * ZenDesk reader interface.
 *
 * Interface ZenDeskReaderInterface
 */
interface ZenDeskReaderInterface extends ReaderInterface
{
    const CODE_UNAUTHORIZED          = 401;
    const CODE_UN_PROCESSABLE_ENTITY = 422;
    const CODE_TOO_MANY_REQUESTS     = 429;
    const CODE_NOT_FOUND             = 404;

    const FIELD_TYPE_SYSTEM_SUBJECT        = 'subject';
    const FIELD_TYPE_SYSTEM_DESCRIPTION    = 'description';
    const FIELD_TYPE_SYSTEM_STATUS         = 'status';
    const FIELD_TYPE_SYSTEM_TICKET_TYPE    = 'tickettype';
    const FIELD_TYPE_SYSTEM_PRIORITY       = 'priority';
    const FIELD_TYPE_SYSTEM_BASIC_PRIORITY = 'basic_priority';
    const FIELD_TYPE_SYSTEM_GROUP          = 'group';
    const FIELD_TYPE_SYSTEM_ASSIGNEE       = 'assignee';
    const FIELD_TYPE_TAGGER                = 'tagger';
    const FIELD_TYPE_CHECKBOX              = 'checkbox';
    const FIELD_TYPE_DATE                  = 'date';
    const FIELD_TYPE_DECIMAL               = 'decimal';
    const FIELD_TYPE_DROPDOWN              = 'dropdown';
    const FIELD_TYPE_INTEGER               = 'integer';
    const FIELD_TYPE_REGEXP                = 'regexp';
    const FIELD_TYPE_TEXT                  = 'text';
    const FIELD_TYPE_TEXTAREA              = 'textarea';

    /**
     * Returns account settings.
     *
     * @return array
     */
    public function getSettings();

    /**
     * Returns a batch count of users.
     *
     * @param DateTime $start_time
     *
     * @throws RetryAfterException
     *
     * @return int
     */
    public function getPeopleCount(DateTime $start_time = null);

    /**
     * Returns a batch of the users collection.
     *
     * @param DateTime $start_time
     *
     * @throws RetryAfterException
     *
     * @return array
     */
    public function getPeople(DateTime $start_time = null);

    /**
     * Returns a batch end time of the users collection.
     *
     * @param DateTime $start_time
     *
     * @throws RetryAfterException
     *
     * @return DateTime
     */
    public function getPeopleEndTime(DateTime $start_time = null);

    /**
     * Returns an user by id.
     *
     * @param array $id
     *
     * @return array
     */
    public function getPersonById($id);

    /**
     * Returns a batch of the users collection of certain ids.
     *
     * @param array $ids
     *
     * @throws RetryAfterException
     *
     * @return array
     */
    public function getPeopleByIds(array $ids);

    /**
     * Returns user fields collection.
     *
     * @throws RetryAfterException
     *
     * @return array
     */
    public function getPeopleFields();

    /**
     * Returns organizations collection.
     *
     * @return array
     */
    public function getOrganizations();

    /**
     * Returns an organization by id.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function getOrganizationById($id);

    /**
     * Returns organization fields collection.
     *
     * @return mixed
     */
    public function getOrganizationFields();

    /**
     * Returns a batch count of users tickets.
     *
     * @param DateTime $start_time
     *
     * @throws RetryAfterException
     *
     * @return int
     */
    public function getTicketsCount(DateTime $start_time = null);

    /**
     * Returns a batch of the tickets collection.
     *
     * @param DateTime $start_time
     *
     * @throws RetryAfterException
     *
     * @return array
     */
    public function getTickets(DateTime $start_time = null);

    /**
     * Returns a batch end time of the tickets collection.
     *
     * @param DateTime $start_time
     *
     * @throws RetryAfterException
     *
     * @return DateTime
     */
    public function getTicketsEndTime(DateTime $start_time = null);

    /**
     * Returns a collection of ticket comments.
     *
     * @param int $id
     *
     * @return array
     */
    public function getTicketComments($id);

    /**
     * Returns ticket fields collection.
     * Returns a batch end time of the tickets collection.
     *
     * @throws RetryAfterException
     *
     * @return array
     */
    public function getTicketFields();

    /**
     * Returns a batch count of articles.
     *
     * @param DateTime|null $start_time
     *
     * @return int
     */
    public function getArticlesCount(DateTime $start_time = null);

    /**
     * Returns a batch of the articles collection.
     *
     * @param DateTime|null $start_time
     *
     * @return array
     */
    public function getArticles(DateTime $start_time = null);

    /**
     * Returns a batch end time of the articles collection.
     *
     * @param DateTime $start_time
     *
     * @throws RetryAfterException
     *
     * @return DateTime
     */
    public function getArticlesEndTime(DateTime $start_time = null);

    /**
     * Returns a collection of article comments.
     *
     * @param int $id
     *
     * @return array
     */
    public function getArticleComments($id);

    /**
     * Returns a collection of article attachments.
     *
     * @param int $id
     *
     * @return array
     */
    public function getArticleAttachments($id);

    /**
     * Returns a collection of article translations.
     *
     * @param $id
     *
     * @return array
     */
    public function getArticleTranslations($id);

    /**
     * Returns article category path like "Category Name > Section Name".
     *
     * @param int $section_id
     *
     * @return string
     */
    public function getArticleCategoryPath($section_id);

    /**
     * Returns a collection of article categories.
     *
     * @return array
     */
    public function getArticlesCategories();

    /**
     * Returns a collection of article sub categories.
     *
     * @return array
     */
    public function getArticlesSections();
}
