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

use Application\DeskPRO\Searcher\PeopleSearch;
use Application\DeskPRO\Entity\ResultCache;
use Application\DeskPRO\Entity\People;
use Application\DeskPRO\Entity;
use Application\DeskPRO\App;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Handles people searches
 */
class PeopleResults
{
	/**
	 * @var Application\AgentBundle\Controller\AbstractController
	 */
	protected $controller;

	/**
	 * @var array
	 */
	protected $people_ids = array();

	/**
	 * @var array
	 */
	protected $order_by = null;

	/**
	 * @return Application\AgentBundle\Controller\Helper\PeopleResults
	 */
	public static function newFromResultCache($controller, ResultCache $result_cache)
	{
		$helper = new self($controller);
		$helper->setPeopleIds($result_cache['results']);

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
	public function setPeopleIds(array $people_ids)
	{
		$this->people_ids = $people_ids;
	}



	/**
	 * Get people IDs
	 *
	 * @return array
	 */
	public function getPeopleIds()
	{
		return $this->people_ids;
	}


	/**
	 * Get people for a particular page
	 *
	 * @return array
	 */
	public function getPeopleForPage($page, $per_page = 50)
	{
		return $this->_getPageFromPeopleIds($this->getPeopleIds(), $page, $per_page);
	}



	protected function _getPageFromPeopleIds(array $people_ids, $page, $per_page)
	{
		$page_people_ids = Arrays::getPageChunk($people_ids, $page, $per_page);
		$people_raw = App::getEntityRepository('DeskPRO:Person')->getPeopleResultsFromIds($page_people_ids);

		// - We'll get a page of results, but that actual page isn't going to be
		// sorted the way we want, because MySQL was just sent a list of ID's.
		// - So we'll re-create the array here according to the order they're supposed to be in.
		$people = array();
		foreach ($people_ids as $tid) {
			if (isset($people_raw[$tid])) {
				$people[$tid] = $people_raw[$tid];
			}
		}

		return $people;
	}
}
