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

		return $this->render('TechBundle:PeopleSearch:index', array(
			'people_list' => $people_list
		));
	}
}