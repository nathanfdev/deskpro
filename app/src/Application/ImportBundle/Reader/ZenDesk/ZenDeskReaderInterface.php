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

namespace Application\ImportBundle\Reader\ZenDesk;

use Application\ImportBundle\Reader\ReaderInterface;
use DateTime;

/**
 * ZenDesk reader interface
 *
 * Interface ZenDeskReaderInterface
 * @package Application\ImportBundle\Reader\ZenDesk
 */
interface ZenDeskReaderInterface extends ReaderInterface
{
    const CODE_UNAUTHORIZED          = 401;
    const CODE_UN_PROCESSABLE_ENTITY = 422;
    const CODE_TOO_MANY_REQUESTS     = 429;

    /**
     * Returns a batch count of users
     *
     * @param DateTime $start_time
     *
     * @return int
     * @throws RetryAfterException
     */
    public function getPeopleCount(DateTime $start_time = null);

    /**
     * Returns a batch of the users collection
     *
     * @param DateTime $start_time
     *
     * @return array
     * @throws RetryAfterException
     */
    public function getPeople(DateTime $start_time = null);

    /**
     * Returns a batch end time of the users collection
     *
     * @param DateTime $start_time
     *
     * @return DateTime
     * @throws RetryAfterException
     */
    public function getPeopleEndTime(DateTime $start_time = null);

    /**
     * Returns a batch of the users collection of certain ids
     *
     * @param array $ids
     *
     * @return array
     * @throws RetryAfterException
     */
    public function getPeopleByIds(array $ids);

    /**
     * Returns an organization by id
     *
     * @param int $id
     * @return mixed
     */
    public function getOrganizationById($id);

    /**
     * Returns a batch count of users tickets
     *
     * @param DateTime $start_time
     *
     * @return int
     * @throws RetryAfterException
     */
    public function getTicketsCount(DateTime $start_time = null);

    /**
     * Returns a batch of the tickets collection
     *
     * @param DateTime $start_time
     *
     * @return array
     * @throws RetryAfterException
     */
    public function getTickets(DateTime $start_time = null);

    /**
     * Returns a collection of ticket comments
     *
     * @param int $id
     * @return array
     */
    public function getTicketComments($id);

    /**
     * Returns a batch end time of the tickets collection
     *
     * @param DateTime $start_time
     *
     * @return DateTime
     * @throws RetryAfterException
     */
    public function getTicketsEndTime(DateTime $start_time = null);

    /**
     * Returns a batch count of articles
     *
     * @param DateTime|null $start_time
     * @return int
     */
    public function getArticlesCount(DateTime $start_time = null);

    /**
     * Returns a batch of the articles collection
     *
     * @param DateTime|null $start_time
     * @return array
     */
    public function getArticles(DateTime $start_time = null);

    /**
     * Returns a batch end time of the articles collection
     *
     * @param DateTime $start_time
     *
     * @return DateTime
     * @throws RetryAfterException
     */
    public function getArticlesEndTime(DateTime $start_time = null);

    /**
     * Returns a collection of article comments
     *
     * @param int $id
     * @return array
     */
    public function getArticleComments($id);

    /**
     * Returns a collection of article attachments
     *
     * @param int $id
     * @return array
     */
    public function getArticleAttachments($id);

    /**
     * Returns a collection of article translations
     *
     * @param $id
     * @return array
     */
    public function getArticleTranslations($id);

    /**
     * Returns a collection of article categories
     * Merges help center categories and sections
     *
     * @param int $section_id
     * @return array
     */
    public function getArticleCategory($section_id);

    /**
     * Returns a collection of article categories
     *
     * @return array
     */
    public function getArticlesCategories();

    /**
     * Returns a collection of article sub categories
     *
     * @return array
     */
    public function getArticlesSections();
}
