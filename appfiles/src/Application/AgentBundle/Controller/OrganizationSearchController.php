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

use \Application\DeskPRO\Searcher\OrganizationSearch;
use \Application\DeskPRO\Entity\Ticket;
use \Application\DeskPRO\Entity;
use \Application\DeskPRO\App;
use \Orb\Util\Strings;
use \Orb\Util\Arrays;

/**
 * Handles searching for orgs
 */
class OrganizationSearchController extends AbstractController
{
	protected function _getResponseForOrgs($type, $type_id, $results_helper, array $vars = array())
	{
		$is_partial = false;
		$tpl = 'AgentBundle:OrganizationSearch:'.$type.'-results.html.twig';
		if ($this->in->getBool('partial')) {
			$is_partial = true;
			$tpl = 'AgentBundle:OrganizationSearch:part-results-list.html.twig';
		}

		#------------------------------
		# Get the results to show
		#------------------------------

		$page = $this->in->getUint('page');
		if (!$page) $page = 1;

		$people = $results_helper->getOrgsForPage($page);

		#------------------------------
		# Send results
		#------------------------------

		if (!count($people) && $is_partial) {
			return $this->createJsonResponse(array('no_more_results' => true));
		}

		if (empty($vars['display_fields'])) {
			$vars['display_fields'] = array('email_address');
		}

		// person defs for columns
		$org_field_defs = App::getApi('custom_fields.organizations')->getEnabledFields();

		$vars = array_merge($vars, array(
			'type'               => $type,
			'type_id'            => $type_id,
			'organizations'      => $organizations,
			'page'               => $page,
			'org_field_defs'     => $org_field_defs,
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
			if ($result_cache['person_id'] != $this->person['id']) {
				$result_cache = false;
			}
		}

		#------------------------------
		# If there's no result set, we're running it for the first time
		#------------------------------

		if (!$result_cache) {
			$terms = $this->in->getCleanValueArray('terms', 'raw' , 'discard');

			$searcher = new \Application\DeskPRO\Searcher\OrganizationSearch();
			foreach ($terms as $term) {
				$data = $term;
				unset($data['rule_type'], $data['op']);

				if (count($data) == 1) {
					$data = array_pop($data);
				}

				$searcher->addTerm($term['rule_type'], $term['op'], $data);
			}

			$order_by = $this->in->getString('filter.order_by');

			if ($order_by) {
				$searcher->setOrderByCode($order_by);
			}

			$results = $searcher->getMatches();

			$result_cache = new Entity\ResultCache();
			$result_cache['person'] = $this->person;
			$result_cache['criteria'] = array('terms' => $searcher->getTerms(), 'order_by' => $order_by);
			$result_cache['results'] = $results;
			$result_cache['num_results'] = count($results);

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

			$searcher = new \Application\DeskPRO\Searcher\OrganizationSearch();
			$searcher->setTerms($result_cache['criteria']['terms']);
			$searcher->setOrderByCode($result_cache['criteria']['order_by']);

			$results = $searcher->getMatches();
			$result_cache['results'] = $results;
			$result_cache['num_results'] = count($results);

			App::getOrm()->persist($result_cache);
			App::getOrm()->flush();
		}

		#------------------------------
		# Serve results
		#------------------------------

		$results_helper = Helper\OrganizationResults::newFromResultCache($this, $result_cache);

		$vars = array(
			'cache' => $result_cache,
			'cache_id' => $result_cache['id']
		);

		if (!empty($result_cache['extra']['display_fields'])) {
			$vars['display_fields'] =$result_cache['extra']['display_fields'];
		}

		$pref_name = 'agent.ui.org-filter-display-fields.' . $result_cache['id'];
		if (!empty($result_cache['extra'][$pref_name])) {
			$vars['display_fields'] = $result_cache['extra'][$pref_name];
		}

		if ($this->in->getString('page_title')) {
			$vars['page_title'] = $this->in->getString('page_title');
		}

		return $this->_getResponseForOrgs('custom-filter', $result_cache['id'], $results_helper, $vars);
	}

	############################################################################
	# org-labels-pane
	############################################################################

	public function labelsPaneAction()
	{
		$label_counts = App::getEntityRepository('DeskPRO:LabelDef')->getLabelCounts('organizations', 25);
		$cloud_gen = new \Application\DeskPRO\UI\TagCloud($label_counts);
		$cloud = $cloud_gen->getCloud();

		return $this->render('AgentBundle:OrganizationSearch:pane-org-labels.html.twig', array(
			'cloud' => $cloud
		));
	}

	public function labelsIndexPaneAction()
	{
		$label_lister = new \Application\DeskPRO\Labels\LabelLister('organizations');
		$index = $label_lister->getIndexList();

		return $this->render('AgentBundle:OrganizationSearch:pane-org-labels-index.html.twig', array(
			'labels_index' => $index
		));
	}
}