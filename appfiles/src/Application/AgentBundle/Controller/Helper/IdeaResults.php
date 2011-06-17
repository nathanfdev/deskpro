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

use Application\DeskPRO\Searcher\IdeaSearch;
use Application\DeskPRO\UI\RuleBuilder;
use Application\DeskPRO\Entity\ResultCache;
use Application\DeskPRO\Entity\Idea;
use Application\DeskPRO\App;
use Orb\Util\Strings;
use Orb\Util\Arrays;

class IdeaResults
{
	/**
	 * @var Application\AgentBundle\Controller\AbstractController
	 */
	protected $controller;

	/**
	 * @var array
	 */
	protected $idea_ids = array();

	/**
	 * @var array
	 */
	protected $order_by = null;

	/**
	 * @var \Application\DeskPRO\Entity\ResultCache
	 */
	protected $result_cache;

	/**
	 * $options can have:
	 * - default_terms: For when viewing the page that you havent submitted
	 * - specific_terms: Always added to the search
	 * - default_order_by: The default order by for a page you havent submitted
	 * 
	 * @param  $controller
	 * @param array $options
	 * @return \Application\AgentBundle\Controller\Helper\IdeaResults
	 */
	public static function newFromRequest($controller, array $options = array())
	{
		$result_cache = false;
		if ($controller->in->getUint('cache_id')) {
			$result_cache = App::getEntityRepository('DeskPRO:ResultCache')->find($controller->in->getUint('cache_id'));
			if ($result_cache['person_id'] != $controller->person['id']) {
				$result_cache = false;
			}
		}

		#------------------------------
		# If there's no result set, we're running it for the first time
		#------------------------------

		if (!$result_cache) {
			$term_rules = RuleBuilder::newTermsBuilder();

			$form_terms = $controller->in->getCleanValueArray('terms', 'raw' , 'discard');
			$form_terms = Arrays::removeFalsey($form_terms);

			if (!$form_terms AND !empty($options['default_terms'])) {
				$form_terms = $default_terms;
			}

			if (!empty($options['specific_terms'])) {
				$form_terms = array_merge($form_terms, $options['specific_terms']);
			}

			$terms = $term_rules->readForm($form_terms);
			
			$searcher = new IdeaSearch();
			foreach ($terms as $term) {
				$searcher->addTerm($term['type'], $term['op'], $term['options']);
			}

			$order_by = $controller->in->getString('order_by');

			if ($order_by) {
				$searcher->setOrderByCode($order_by);
			} elseif (!empty($options['default_order_by'])) {
				$searcher->setOrderByCode($options['default_order_by']);
			}

			$results = $searcher->getMatches();

			$result_cache = new ResultCache();
			$result_cache['person'] = $controller->person;
			$result_cache['criteria'] = array('terms' => $searcher->getTerms(), 'order_by' => $order_by);
			$result_cache['results'] = $results;
			$result_cache['num_results'] = count($results);

			App::getOrm()->persist($result_cache);
			App::getOrm()->flush();
		}

		return new self($controller, $result_cache);
	}


	public function __construct($controller, ResultCache $result_cache = null)
	{
		$this->controller = $controller;

		if ($result_cache) {
			$this->result_cache = $result_cache;
			$this->setIdeaIds($result_cache['results']);
		}
	}


	/**
	 * @return \Application\DeskPRO\Entity\ResultCache
	 */
	public function getResultCache()
	{
		return $this->result_cache;
	}


	/**
	 * Set ticket IDs for the search results
	 * @param array $idea_ids
	 */
	public function setIdeaIds(array $idea_ids)
	{
		$this->idea_ids = $idea_ids;
	}


	/**
	 * Get ticket IDs
	 *
	 * @return array
	 */
	public function getIdeaIds()
	{
		return $this->idea_ids;
	}


	/**
	 * Get tickets for a particular page
	 *
	 * @return array
	 */
	public function getIdeasForPage($page, $per_page = 50)
	{
		return $this->_getPageFromIdeaIds($this->getIdeaIds(), $page, $per_page);
	}


	protected function _getPageFromIdeaIds(array $idea_ids, $page, $per_page)
	{
		$page_idea_ids = Arrays::getPageChunk($idea_ids, $page, $per_page);
		$ideas_raw = App::getEntityRepository('DeskPRO:Idea')->getByIds($page_idea_ids);

		// - We'll get a page of results, but that actual page isn't going to be
		// sorted the way we want, because MySQL was just sent a list of ID's.
		// - So we'll re-create the array here according to the order they're supposed to be in.
		$ideas = array();
		foreach ($idea_ids as $tid) {
			if (isset($ideas_raw[$tid])) {
				$ideas[$tid] = $ideas_raw[$tid];
			}
		}

		return $ideas;
	}
}