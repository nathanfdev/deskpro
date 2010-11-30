<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage TechBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\TechBundle\Controller;

use \Application\CoreBundle\Entity\Person;
use \Application\CoreBundle\Entity\FormField;
use \Application\CoreBundle\Entity\FormFieldAssociation;

/**
 * Handles searching for people
 */
class PeopleSearchController extends AbstractController
{
	############################################################################
	# /tech/people                                                   tech_people
	############################################################################

	public function indexAction()
	{
		$people_list = $this->em->createQuery("
			SELECT p, p_email
			FROM CoreBundle:Person p
			LEFT JOIN p.primary_email p_email
			ORDER BY p.id DESC
		")->getResult();

		return $this->render('TechBundle:PeopleSearch:index.twig', array(
			'people_list' => $people_list
		));
	}

	############################################################################
	# /tech/people-search                                      tech_peoplesearch
	############################################################################

	public function searchAction()
	{
		return $this->render('TechBundle:PeopleSearch:search.twig');
	}

	############################################################################
	# /tech/people-search/search                       tech_peoplesearch_perform
	############################################################################

	public function performSearchAction()
	{
		$people_list = $this->em->createQuery("
			SELECT p, p_email
			FROM CoreBundle:Person p
			LEFT JOIN p.primary_email p_email
			ORDER BY p.id DESC
		")->getResult();

		return $this->render('TechBundle:PeopleSearch:search_results.twig', array(
			'people_list' => $people_list
		));
	}

	############################################################################
	# /tech/people-search/quick-search            tech_peoplesearch_performquick
	############################################################################

	public function performQuickSearchAction()
	{
		$q = $this->in->getString('q');

		//TODO proper sql escape
		$q = addslashes($q);

		$people_list = $this->em->createQuery("
			SELECT p, p_email
			FROM CoreBundle:Person p
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

		return $this->render('TechBundle:PeopleSearch:search_results.twig', array(
			'people_list' => $people_list
		));
	}

	############################################################################
	# /tech/people-search/labels-pane               tech_peoplesearch_labelspane
	############################################################################

	public function labelsPaneAction()
	{
		return $this->render('TechBundle:PeopleSearch:pane-labels.twig');
	}
}