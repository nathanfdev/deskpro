<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AgentBundle\Controller\Helper;

use Application\DeskPRO\Searcher\OrganizationSearch;
use Application\DeskPRO\Entity\ResultCache;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity;
use Application\DeskPRO\App;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Handles org searches
 */
class OrganizationResults
{
	/**
	 * @var Application\AgentBundle\Controller\AbstractController
	 */
	protected $controller;

	/**
	 * @var array
	 */
	protected $organization_ids = array();

	/**
	 * @var array
	 */
	protected $order_by = null;

	/**
	 * @return Application\AgentBundle\Controller\Helper\OrganizationResults
	 */
	public static function newFromResultCache($controller, ResultCache $result_cache)
	{
		$helper = new self($controller);
		$helper->setOrganizationIds($result_cache['results']);

		return $helper;
	}



	public function __construct($controller)
	{
		$this->controller = $controller;
	}



	/**
	 * Set people IDs for the search results
	 * @param array $people_ids
	 */
	public function setOrganizationIds(array $people_ids)
	{
		$this->organization_ids = $people_ids;
	}



	/**
	 * Get people IDs
	 *
	 * @return array
	 */
	public function getOrganizationIds()
	{
		return $this->organization_ids;
	}


	/**
	 * Get people for a particular page
	 *
	 * @return array
	 */
	public function getOrgsForPage($page, $per_page = 50)
	{
		return $this->_getPageFromOrgsIds($this->getOrganizationIds(), $page, $per_page);
	}



	protected function _getPageFromOrgsIds(array $org_ids, $page, $per_page)
	{
		$page_org_ids = Arrays::getPageChunk($org_ids, $page, $per_page);
		$orgs_raw = App::getEntityRepository('DeskPRO:Organization')->getOrganizationsFromIds($page_org_ids);

		// - We'll get a page of results, but that actual page isn't going to be
		// sorted the way we want, because MySQL was just sent a list of ID's.
		// - So we'll re-create the array here according to the order they're supposed to be in.
		$orgs = array();
		foreach ($org_ids as $tid) {
			if (isset($orgs_raw[$tid])) {
				$orgs[$tid] = $orgs_raw[$tid];
			}
		}

		return $orgs;
	}
}
