<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Controller\Helper;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\People;
use Application\DeskPRO\Entity\ResultCache;
use Orb\Util\Arrays;

/**
 * Handles people searches.
 */
class PeopleResults
{
    const PER_PAGE_DEFAULT = 50;

    /**
     * @var \Application\AgentBundle\Controller\AbstractController
     */
    protected $controller;

    /**
     * @var array
     */
    protected $people_ids = [];

    /**
     * @var array
     */
    protected $order_by = null;

    /**
     * @var int
     */
    protected $perPage;

    /**
     * @return \Application\AgentBundle\Controller\Helper\PeopleResults
     */
    public static function newFromResultCache($controller, ResultCache $result_cache)
    {
        $helper = new self($controller);
        $helper->setPeopleIds($result_cache['results']);

        return $helper;
    }

    public function __construct($controller, $resultsPerPage = self::PER_PAGE_DEFAULT)
    {
        $this->controller = $controller;
        $this->perPage    = $resultsPerPage;
    }

    public function getPerPageCount()
    {
        return $this->perPage;
    }

    /**
     * Set people IDs for the search results.
     *
     * @param array $people_ids
     */
    public function setPeopleIds(array $people_ids)
    {
        $this->people_ids = $people_ids;
    }

    /**
     * Get people IDs.
     *
     * @return array
     */
    public function getPeopleIds()
    {
        return $this->people_ids;
    }

    /**
     * Get people for a particular page.
     *
     * @return array
     */
    public function getPeopleForPage($page)
    {
        return $this->_getPageFromPeopleIds($this->getPeopleIds(), $page, $this->getPerPageCount());
    }

    protected function _getPageFromPeopleIds(array $people_ids, $page, $per_page)
    {
        $page_people_ids = Arrays::getPageChunk($people_ids, $page, $per_page);
        $people_raw      = App::getEntityRepository('DeskPRO:Person')->getPeopleResultsFromIds($page_people_ids);

        // - We'll get a page of results, but that actual page isn't going to be
        // sorted the way we want, because MySQL was just sent a list of ID's.
        // - So we'll re-create the array here according to the order they're supposed to be in.
        $people = [];
        foreach ($people_ids as $tid) {
            if (isset($people_raw[$tid])) {
                $people[$tid] = $people_raw[$tid];
            }
        }

        return $people;
    }
}
