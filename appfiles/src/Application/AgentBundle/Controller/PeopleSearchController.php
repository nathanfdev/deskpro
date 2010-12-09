<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\AgentBundle\Controller;

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

		return $this->render('AgentBundle:PeopleSearch:index.twig', array(
			'people_list' => $people_list
		));
	}

	############################################################################
	# /agent/people-search                                      agent_peoplesearch
	############################################################################

	public function searchAction()
	{
		return $this->render('AgentBundle:PeopleSearch:search.twig');
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

		return $this->render('AgentBundle:PeopleSearch:search_results.twig', array(
			'people_list' => $people_list
		));
	}

	############################################################################
	# /agent/people-search/quick-search            agent_peoplesearch_performquick
	############################################################################

	public function performQuickSearchAction()
	{
		$q = $this->in->getString('q');

		//TODO proper sql escape
		$q = addslashes($q);

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
			ORDER BY p.id DESC
		")->getResult();
		//")->setParameters(array($q, $q))->getResult();

		return $this->render('AgentBundle:PeopleSearch:search_results.twig', array(
			'people_list' => $people_list
		));
	}

	############################################################################
	# /agent/people-search/labels-pane               agent_peoplesearch_labelspane
	############################################################################

	public function labelsPaneAction()
	{
		return $this->render('AgentBundle:PeopleSearch:pane-labels.twig');
	}
}