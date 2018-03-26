<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Controller\Helper;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\ResultCache;
use Orb\Util\Arrays;

/**
 * Handles org searches.
 */
class OrganizationResults
{
    const PER_PAGE_DEFAULT = 50;

    /**
     * @var \Application\AgentBundle\Controller\AbstractController
     */
    protected $controller;

    /**
     * @var array
     */
    protected $organization_ids = [];

    /**
     * @var array
     */
    protected $order_by = null;

    /**
     * @var int
     */
    protected $perPage;

    /**
     * @return \Application\AgentBundle\Controller\Helper\OrganizationResults
     */
    public static function newFromResultCache($controller, ResultCache $result_cache)
    {
        $helper = new self($controller);
        $helper->setOrganizationIds($result_cache['results']);

        return $helper;
    }

    public function __construct($controller, $perPage = self::PER_PAGE_DEFAULT)
    {
        $this->controller = $controller;
        $this->perPage    = $perPage;
    }

    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * Set people IDs for the search results.
     *
     * @param array $people_ids
     */
    public function setOrganizationIds(array $people_ids)
    {
        $this->organization_ids = $people_ids;
    }

    /**
     * Get people IDs.
     *
     * @return array
     */
    public function getOrganizationIds()
    {
        return $this->organization_ids;
    }

    /**
     * Get people for a particular page.
     *
     * @return array
     */
    public function getOrgsForPage($page)
    {
        return $this->_getPageFromOrgsIds($this->getOrganizationIds(), $page, $this->getPerPage());
    }

    protected function _getPageFromOrgsIds(array $org_ids, $page, $per_page)
    {
        $page_org_ids = Arrays::getPageChunk($org_ids, $page, $per_page);
        $orgs_raw     = App::getEntityRepository('DeskPRO:Organization')->getOrganizationsFromIds($page_org_ids);

        // - We'll get a page of results, but that actual page isn't going to be
        // sorted the way we want, because MySQL was just sent a list of ID's.
        // - So we'll re-create the array here according to the order they're supposed to be in.
        $orgs = [];
        foreach ($org_ids as $tid) {
            if (isset($orgs_raw[$tid])) {
                $orgs[$tid] = $orgs_raw[$tid];
            }
        }

        return $orgs;
    }
}
