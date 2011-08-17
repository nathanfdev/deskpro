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

namespace Application\AgentBundle\Controller;

use \Application\DeskPRO\Searcher\TicketSearch;
use \Application\DeskPRO\Entity\TicketFilter;
use \Application\DeskPRO\Entity\Ticket;
use \Application\DeskPRO\Entity;
use \Application\DeskPRO\App;
use \Orb\Util\Strings;
use \Orb\Util\Arrays;

/**
 * Handles searching for people
 */
class PeopleSearchController extends AbstractController
{
	public function getSectionDataAction()
	{
		$data = array();

		#------------------------------
		# People labels
		#------------------------------

		$label_counts = App::getEntityRepository('DeskPRO:LabelDef')->getLabelCounts('people', 25);
		$cloud_gen = new \Application\DeskPRO\UI\TagCloud($label_counts);
		$people_tag_cloud = $cloud_gen->getCloud();

		$label_lister = new \Application\DeskPRO\Labels\LabelLister('people');
		$people_tag_index = $label_lister->getIndexList();

		#------------------------------
		# Org labels
		#------------------------------

		$label_counts = App::getEntityRepository('DeskPRO:LabelDef')->getLabelCounts('organizations', 25);
		$cloud_gen = new \Application\DeskPRO\UI\TagCloud($label_counts);
		$org_tag_cloud = $cloud_gen->getCloud();

		$label_lister = new \Application\DeskPRO\Labels\LabelLister('organizations');
		$org_tag_index = $label_lister->getIndexList();

		$data['section_html'] = $this->renderView('AgentBundle:PeopleSearch:window-section.html.twig', array(
			'people_tag_cloud' => $people_tag_cloud,
			'people_tag_index' => $people_tag_index,
			'org_tag_cloud'    => $org_tag_cloud,
			'org_tag_index'    => $org_tag_index
		));

		return $this->createJsonResponse($data);
	}

	protected function _getResponseForPeople($type, $type_id, $results_helper, array $vars = array())
	{
		$view_type = $this->in->getString('view_type');
		if (!$view_type OR !in_array($view_type, array('list', 'simple'))) {
			$view_type = 'simple';
		}

		$is_partial = false;
		$tpl = 'AgentBundle:PeopleSearch:'.$type.'-results-'.$view_type.'.html.twig';
		if ($this->in->getBool('partial')) {
			$is_partial = true;
			$tpl = 'AgentBundle:PeopleSearch:part-results-'.$view_type.'.html.twig';
		}

		#------------------------------
		# Get the tickets to show
		#------------------------------

		$page = $this->in->getUint('page');
		if (!$page) $page = 1;

		$people = $results_helper->getPeopleForPage($page);

		#------------------------------
		# Send results
		#------------------------------

		if (!count($people) && $is_partial) {
			return $this->createJsonResponse(array('no_more_results' => true));
		}

		if (empty($vars['display_fields'])) {
			$vars['display_fields'] = array('email_address');
		}
		
		$vars['display_fields'] = Arrays::removeFalsey($vars['display_fields']);
		$vars['display_fields'] = array_unique($vars['display_fields']);

		// person defs for columns
		$person_field_defs = App::getApi('custom_fields.people')->getEnabledFields();

		$vars = array_merge($vars, array(
			'type'               => $type,
			'type_id'            => $type_id,
			'people'             => $people,
			'page'               => $page,
			'person_field_defs'  => $person_field_defs,
			'load_first'         => $this->in->getBool('load_first')
		));

		$html = $this->renderView($tpl, $vars);

		if ($is_partial) {
			return $this->createJsonResponse(array(
				'html'              => $html,
				'page'              => $page,
			));
		} else {
			return $this->createResponse($html);
		}
	}


	############################################################################
	# search
	############################################################################

	public function searchAction()
	{
		$result_cache = false;
		if ($this->in->getUint('cache_id')) {
			$result_cache = App::getEntityRepository('DeskPRO:ResultCache')->find($this->in->getUint('cache_id'));
			if (!$result_cache OR $result_cache['person_id'] != $this->person['id']) {
				$result_cache = false;
			}
		}

		#------------------------------
		# If there's no result set, we're running it for the first time
		#------------------------------

		if (!$result_cache) {

			$old_result_cache = false;
			if ($this->in->getUint('copy_display_options')) {
				$old_result_cache = App::getEntityRepository('DeskPRO:ResultCache')->find($this->in->getUint('copy_display_options'));
				if (!$old_result_cache OR $old_result_cache['person_id'] != $this->person['id']) {
					$old_result_cache = false;
				}
			}

			$terms = $this->in->getCleanValueArray('terms', 'raw' , 'discard');

			$searcher = new \Application\DeskPRO\Searcher\PersonSearch();
			foreach ($terms as $term) {
				$data = $term;
				unset($data['rule_type'], $data['op']);

				if (count($data) == 1) {
					$data = array_pop($data);
				}

				$searcher->addTerm($term['rule_type'], $term['op'], $data);
			}

			$order_by = $this->in->getString('filter.order_by');

			if ($old_result_cache AND isset($result_cache['extra']['order_by'])) {
				$order_by = $result_cache['extra']['order_by'];
			}

			if ($order_by) {
				$searcher->setOrderByCode($order_by);
			}

			$results = $searcher->getMatches();

			$result_cache = new Entity\ResultCache();
			$result_cache['person'] = $this->person;
			$result_cache['criteria'] = array('terms' => $searcher->getTerms(), 'order_by' => $order_by);
			$result_cache['results'] = $results;
			$result_cache['num_results'] = count($results);
			$result_cache->setExtraData('terms_summary', $searcher->getSummary());

			if ($old_result_cache) {
				$result_cache['extra'] = $old_result_cache['extra'];
			}

			App::getOrm()->persist($result_cache);
			App::getOrm()->flush();
		}

		#------------------------------
		# Re-do search if we changed order
		#------------------------------

		// Prefs are saved into extra[]. Of order_by doesn't match
		// the order_by in criteria, that means the user changed it
		// and we have to re-do the search

		if (!empty($result_cache['extra']['order_by']) AND $result_cache['extra']['order_by'] != $result_cache['criteria']['order_by']) {
			$criteria = $result_cache['criteria'];
			$criteria['order_by'] = $result_cache['extra']['order_by'];

			$result_cache['criteria'] = $criteria;

			$searcher = new \Application\DeskPRO\Searcher\PersonSearch();
			$searcher->setTerms($result_cache['criteria']['terms']);
			$searcher->setOrderByCode($result_cache['criteria']['order_by']);

			$results = $searcher->getMatches();
			$result_cache['results'] = $results;
			$result_cache['num_results'] = count($results);
			$result_cache->setExtraData('terms_summary', $searcher->getSummary());

			App::getOrm()->persist($result_cache);
			App::getOrm()->flush();
		}

		#------------------------------
		# Serve results
		#------------------------------

		$results_helper = Helper\PeopleResults::newFromResultCache($this, $result_cache);

		$vars = array(
			'cache' => $result_cache,
			'cache_id' => $result_cache['id'],
			'terms_summary' => $result_cache->getExtraData('terms_summary')
		);

		if (!empty($result_cache['extra']['display_fields'])) {
			$vars['display_fields'] = $result_cache['extra']['display_fields'];
		}

		$pref_display_fields = $this->person->getPref('agent.ui.people-filter-display-fields.' . $result_cache['id']);
		if ($pref_display_fields) {
			$vars['display_fields'] = $pref_display_fields;
		}

		if ($this->in->getString('page_title')) {
			$vars['page_title'] = $this->in->getString('page_title');
		}

		$vars['preselect_terms'] = $result_cache['criteria'];
		$vars['num_results'] = $result_cache['num_results'];

		// Used in the search form again
        $titles = array();
        $titles['organizations'] = App::getEntityRepository('DeskPRO:Organization')->getOrganizationNames();
        $titles['usergroups'] = App::getEntityRepository('DeskPRO:Usergroup')->getUsergroupNames();
        $titles['locales'] = App::getEntityRepository('DeskPRO:Locale')->getLocaleNames();
		$vars['titles'] = $titles;

		$people_field_defs = App::getApi('custom_fields.people')->getEnabledFields();
		$people_fields = App::getApi('custom_fields.people')->getFieldsDisplayArray($people_field_defs);
		$vars['people_fields'] = $people_fields;

		return $this->_getResponseForPeople('custom-filter', $result_cache['id'], $results_helper, $vars);
	}

	############################################################################
	# quick-find
	############################################################################

	public function quickFindAction()
	{
		return $this->render('AgentBundle:PeopleSearch:quick-find.html.twig');
	}

	public function quickFindSearchAction()
	{
		$term_rules = \Application\DeskPRO\UI\RuleBuilder::newTermsBuilder();
		$terms = $term_rules->readForm($this->in->getCleanValueArray('terms', 'raw' , 'discard'));

		$searcher = new \Application\DeskPRO\Searcher\PersonSearch();
		foreach ($terms as $term) {
			$searcher->addTerm($term['type'], $term['op'], $term['options']);
		}

		$results = $searcher->getMatches();

		$data = array();

		if (!$results) {
			$data['no_results'] = true;
		} else {
			$data['num_results'] = count($results);

			$helper = new Helper\PeopleResults($this);
			$helper->setPeopleIds($results);

			$people = $helper->getPeopleForPage(1, 100);

			$data['html'] = $this->renderView('AgentBundle:PeopleSearch:quick-find-results.html.twig', array(
				'people' => $people,
				'page' => 1
			));
		}

		return $this->createJsonResponse($data);
	}

	############################################################################
	# /agent/people-search/quick-search            agent_peoplesearch_performquick
	############################################################################

	public function performQuickSearchAction()
	{
		$q = $this->in->getString('q');
		if (!$q) {
			$q = $this->in->getString('term');
		}

		//TODO proper sql escape
		$q = addslashes($q);

		$limit = $this->in->getUint('limit');
		if (!$limit) $limit = 10;
		$limit = min($limit, 100);

		$people_list = $this->em->createQuery("
			SELECT p, p_email
			FROM DeskPRO:Person p
			LEFT JOIN p.primary_email p_email
			LEFT JOIN p.emails emails
			LEFT JOIN p.organization org
			WHERE
				emails.email LIKE '$q%'
				OR (
					p.name LIKE '%$q%'
					OR p.first_name LIKE '%$q%'
					OR p.last_name LIKE '%$q%'
					OR emails.email LIKE '%$q%'
					OR org.name LIKE '%$q%'
				)
			GROUP BY p.id
			ORDER BY p.last_name ASC, p.first_name ASC, p.name ASC
		")->setMaxResults($limit)->getResult();
		//")->setParameters(array($q, $q))->getResult();

		$format = $this->in->getString('format');

		if ($format == 'json' OR (!$format AND $this->in->getBool('ajax'))) {
			$tpl = "AgentBundle:PeopleSearch:search_results.json.jsonphp";
		} else {
			$tpl = "AgentBundle:PeopleSearch:search_results.html.twig";
			if ($format == 'simplelist') {
				$tpl = "AgentBundle:PeopleSearch:search-results-simplelist.html.twig";
			}
		}

		return $this->render($tpl, array(
			'people_list' => $people_list
		));
	}

	############################################################################
	# labels-pane
	############################################################################

	public function labelsPaneAction()
	{
		$label_counts = App::getEntityRepository('DeskPRO:LabelDef')->getLabelCounts('people', 25);
		$cloud_gen = new \Application\DeskPRO\UI\TagCloud($label_counts);
		$cloud = $cloud_gen->getCloud();

		return $this->render('AgentBundle:PeopleSearch:pane-labels.html.twig', array(
			'cloud' => $cloud
		));
	}

	public function labelsIndexPaneAction()
	{
		$label_lister = new \Application\DeskPRO\Labels\LabelLister('people');
		$index = $label_lister->getIndexList();

		return $this->render('AgentBundle:PeopleSearch:pane-labels-index.html.twig', array(
			'labels_index' => $index
		));
	}

	############################################################################
	# org-labels-pane
	############################################################################

	public function orgLabelsPaneAction()
	{
		$label_counts = App::getEntityRepository('DeskPRO:LabelDef')->getLabelCounts('organizations', 25);
		$cloud_gen = new \Application\DeskPRO\UI\TagCloud($label_counts);
		$cloud = $cloud_gen->getCloud();

		return $this->render('AgentBundle:PeopleSearch:pane-org-labels.html.twig', array(
			'cloud' => $cloud
		));
	}

	public function orgLabelsIndexPaneAction()
	{
		$label_lister = new \Application\DeskPRO\Labels\LabelLister('organizations');
		$index = $label_lister->getIndexList();

		return $this->render('AgentBundle:PeopleSearch:pane-org-labels-index.html.twig', array(
			'labels_index' => $index
		));
	}

	############################################################################
	# usergroups-pane
	############################################################################

	public function usergroupsPaneAction()
	{
		$all_usergroups = App::getEntityRepository('DeskPRO:Usergroup')->getUsergroupNames();

		$usergroup_counts = App::getDb()->fetchAllKeyValue("
			SELECT usergroup_id, COUNT(*)
			FROM person2usergroups
			GROUP BY usergroup_id
		");

		return $this->render('AgentBundle:PeopleSearch:pane-usergroups.html.twig', array(
			'all_usergroups' => $all_usergroups,
			'usergroup_counts' => $usergroup_counts
		));
	}


	public function findPaneAction()
	{
		return $this->render('AgentBundle:PeopleSearch:pane-find.html.twig');
	}
}