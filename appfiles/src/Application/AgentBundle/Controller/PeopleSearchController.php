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

use Application\DeskPRO\Searcher\TicketSearch;
use Application\DeskPRO\Entity\TicketFilter;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity;
use Application\DeskPRO\App;
use Orb\Util\Strings;
use Orb\Util\Arrays;

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

		$people_count = App::getEntityRepository('DeskPRO:Person')->getCount();

		$label_counts = App::getEntityRepository('DeskPRO:LabelDef')->getLabelCounts('people', 25);
		$cloud_gen = new \Application\DeskPRO\UI\TagCloud($label_counts);
		$people_tag_cloud = $cloud_gen->getCloud();

		$label_lister = new \Application\DeskPRO\Labels\LabelLister('people');
		$people_tag_index = $label_lister->getIndexList();

		#------------------------------
		# Org labels
		#------------------------------

		$org_count = App::getEntityRepository('DeskPRO:Organization')->getCount();

		$label_counts = App::getEntityRepository('DeskPRO:LabelDef')->getLabelCounts('organizations', 25);
		$cloud_gen = new \Application\DeskPRO\UI\TagCloud($label_counts);
		$org_tag_cloud = $cloud_gen->getCloud();

		$label_lister = new \Application\DeskPRO\Labels\LabelLister('organizations');
		$org_tag_index = $label_lister->getIndexList();

		$usergroup_names      = App::getEntityRepository('DeskPRO:Usergroup')->getUsergroupNames();
		$usergroup_counts     = App::getEntityRepository('DeskPRO:Usergroup')->getCountsFor(array_keys($usergroup_names));
		$org_usergroup_counts = App::getEntityRepository('DeskPRO:Usergroup')->getCountsFor(array_keys($usergroup_names));

		$data['section_html'] = $this->renderView('AgentBundle:PeopleSearch:window-section.html.twig', array(
			'usergroup_names'      => $usergroup_names,
			'usergroup_counts'     => $usergroup_counts,
			'org_usergroup_counts' => $org_usergroup_counts,

			'people_count'     => $people_count,
			'people_tag_cloud' => $people_tag_cloud,
			'people_tag_index' => $people_tag_index,
			'org_tag_cloud'    => $org_tag_cloud,
			'org_tag_index'    => $org_tag_index,
			'org_count'        => $org_count
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
		$tpl = 'AgentBundle:PeopleSearch:'.$type . ($view_type != 'simple' ? '-'.$view_type : '') .'.html.twig';
		if ($this->in->getBool('partial')) {
			$is_partial = true;
			$tpl = 'AgentBundle:PeopleSearch:' . $type . '-page' . ($view_type != 'simple' ? '-'.$view_type : '') . '.html.twig';
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

			if (!$order_by) {
				$order_by = 'people.id:asc';
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

		$order_pref = $this->person->getPref('agent.ui.people-filter-order-by.' . $result_cache['id']);

		if ($order_pref && $order_pref != $result_cache['criteria']['order_by']) {
			$criteria = $result_cache['criteria'];
			$criteria['order_by'] = $order_pref;

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
		} else {
			$pref_display_fields = $this->person->getPref('agent.ui.people-filter-display-fields.0');
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
        $titles['languages'] = App::getEntityRepository('DeskPRO:Language')->getTitles();
		$vars['titles'] = $titles;

		$people_field_defs = App::getApi('custom_fields.people')->getEnabledFields();
		$people_fields = App::getApi('custom_fields.people')->getFieldsDisplayArray($people_field_defs);
		$vars['people_fields'] = $people_fields;

		return $this->_getResponseForPeople('list', $result_cache['id'], $results_helper, $vars);
	}

	public function massActionsAction($action)
	{
		$this->em->beginTransaction();

		$people = $this->em->getRepository('DeskPRO:Person')->getByIds($this->in->getCleanValueArray('ids', 'uint', 'discard'));

		$organization = null;
		$usergroup = null;

		if ($this->in->getUint('organization_id')) {
			$organization = App::findEntity('DeskPRO:Organization', $this->in->getUint('organization_id'));
		}
		if ($this->in->getUint('usergroup_id')) {
			$usergroup = App::findEntity('DeskPRO:Usergroup', $this->in->getUint('usergroup_id'));
		}


		foreach ($ideas as $idea) {
			switch ($action) {
				case 'delete':
					// todo need way to handle soft-deleted
					break;

				case 'add-to-organization':
					if ($organization) {
						foreach ($people as $p) {
							$p->organization = $organization;
							$this->em->persist($p);
						}
					}
					break;

				case 'del-from-organization':
					foreach ($people as $p) {
						if ($p->organization) {
							$p->organization = null;
							$this->em->persist($p);
						}
					}
					break;

				case 'add-to-usergroup':
					if ($usergroup) {
						foreach ($people as $p) {
							if (!isset($p->usergroups[$usergroup->id])) {
								$p->usergroups->add($usergroup);
								$this->em->persist($p);
							}
						}
					}
					break;

				case 'del-form-usergroup':
					if ($usergroup) {
						foreach ($people as $p) {
							if (isset($p->usergroups[$usergroup->id])) {
								$p->usergroups->remove($usergroup->id);
								$this->em->persist($p);
							}
						}
					}
					break;
			}
		}

		$this->em->flush();
		$this->em->commit();

		return $this->createJsonResponse(array(
			'success' => 1
		));
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

		$limit = $this->in->getUint('limit');
		if (!$limit) $limit = 10;
		$limit = min($limit, 100);

		$not_in_org = $this->in->getUint('exclude_org');

		if (!$q && $this->in->getBool('start_with')) {
			$people_list = App::getDb()->fetchAll("
				SELECT p.id, p.first_name, p.last_name, e.email
				FROM people p
				LEFT JOIN people_emails e ON (e.person_id = p.id)
				" . ($not_in_org ? " WHERE p.organization_id != $not_in_org " : '') . "
				ORDER BY p.name ASC
				LIMIT $limit
			");
		} else {

			$people_list = App::getDb()->fetchAll("
				SELECT p.id, p.first_name, p.last_name, e.email
				FROM people p
				LEFT JOIN people_emails e ON (e.person_id = p.id)
				WHERE
					(e.email LIKE ?
					OR p.name LIKE ?
					OR p.first_name LIKE ?
					OR p.last_name LIKE ?)
					" . ($not_in_org ? " AND p.organization_id != $not_in_org " : '') . "
				GROUP BY p.id
				ORDER BY p.name ASC
				LIMIT $limit
			", array("%$q%", "%$q%", "%$q%", "%$q%"));
		}

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
