<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AdminBundle\Controller;

use \Application\DeskPRO\Entity;
use \Application\DeskPRO\App;
use \Orb\Util\Strings;
use \Orb\Util\Arrays;
use \Orb\Util\Util;

use \Application\AdminBundle\Form\EditOrganizationForm;

class OrganizationsController extends AbstractController
{
	public function listAction()
	{
		$this->rememberLastPage();

		$all_organizations = App::getOrm()->createQuery("
			SELECT o
			FROM DeskPRO:Organization o
			ORDER BY o.name ASC
		")->execute();

		return $this->render('AdminBundle:Organizations:list.html.twig', array(
			'all_organizations' => $all_organizations
		));
	}

	public function editAction($organization_id)
	{
		if (!$organization_id) {
			$organization = new Entity\Organization();
		} else {
			$organization = App::getEntityRepository('DeskPRO:Organization')->find($organization_id);
		}

		$form = EditOrganizationForm::create($this->get('form.context'), 'organization', array('organization' => $organization));
		$form->bind($this->get('request'), $organization);

		$is_edited = false;
		$row_html = false;
		if ($this->in->getBool('process')) {
			$is_edited = true;
			App::getOrm()->persist($organization);
			App::getOrm()->flush();

			$row_html = $this->renderView('AdminBundle:Organizations:list-row.html.twig', array('organization' => $organization));
		}

		return $this->render('AdminBundle:Organizations:edit.html.twig', array(
			'organization' => $organization,
			'form'      => $form,
			'is_edited' => $is_edited,
			'row_html'  => $row_html
		));
	}
}