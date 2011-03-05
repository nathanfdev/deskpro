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

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity\Person;
use \Application\DeskPRO\Entity\FormField;
use \Application\DeskPRO\Entity\FormFieldAssociation;

/**
 * Handles searching for people
 */
class PeopleSearchController extends AbstractController
{
	############################################################################
	# /agent/people                                                   agent_people
	############################################################################

	public function indexAction()
	{
		$people_list = $this->em->createQuery("
			SELECT p, p_email
			FROM DeskPRO:Person p
			LEFT JOIN p.primary_email p_email
			ORDER BY p.id DESC
		")->getResult();

		return $this->render('AgentBundle:PeopleSearch:index.html.twig', array(
			'people_list' => $people_list
		));
	}

	############################################################################
	# /agent/people-search                                      agent_peoplesearch
	############################################################################

	public function searchAction()
	{
		return $this->render('AgentBundle:PeopleSearch:search.html.twig');
	}

	############################################################################
	# /agent/people-search/search                       agent_peoplesearch_perform
	############################################################################

	public function performSearchAction()
	{
		$people_list = $this->em->createQuery("
			SELECT p, p_email
			FROM DeskPRO:Person p
			LEFT JOIN p.primary_email p_email
			ORDER BY p.id DESC
		")->getResult();

		return $this->render('AgentBundle:PeopleSearch:search_results.html.twig', array(
			'people_list' => $people_list
		));
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
				p.name LIKE '%$q%'
				OR p.first_name LIKE '%$q%'
				OR p.last_name LIKE '%$q%'
				OR emails.email LIKE '%$q%'
				OR org.name LIKE '%$q%'
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

	public function labelsIndexPaneAction()
	{
		$label_lister = new \Application\DeskPRO\Labels\LabelLister('people');
		$index = $label_lister->getIndexList();

		return $this->render('AgentBundle:PeopleSearch:pane-labels-index.html.twig', array(
			'labels_index' => $index
		));
	}

	public function usergroupsPaneAction()
	{
		$all_usergroups = App::getEntityRepository('DeskPRO:Usergroup')->getUsergroupNames();

		return $this->render('AgentBundle:PeopleSearch:pane-usergroups.html.twig', array(
			'all_usergroups' => $all_usergroups
		));
	}


	public function findPaneAction()
	{
		return $this->render('AgentBundle:PeopleSearch:pane-find.html.twig');
	}
}