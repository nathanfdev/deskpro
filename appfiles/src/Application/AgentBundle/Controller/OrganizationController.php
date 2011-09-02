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

use \Orb\Util\Arrays;

use \Application\DeskPRO\Entity;
use \Application\DeskPRO\Entity\Organization;
use \Application\DeskPRO\Entity\OrganizationContactData;
use \Application\DeskPRO\Entity\OrganizationNote;

use \Application\DeskPRO\App;

/**
 * Handles viewing and editing an org
 */
class OrganizationController extends AbstractController
{
	public function newOrgFromPaneAction()
	{
		$org = new Organization();
		$org['name'] = $this->in->getString('name');

		App::getOrm()->persist($org);
		App::getOrm()->flush();

		return $this->createJsonResponse(array(
			'organization_id' => $org['id']
		));
	}

	############################################################################
	# view
	############################################################################

	public function viewAction($organization_id)
	{
		$org = $this->getOrgOr404($organization_id);

		// Custom fields
		$field_defs = App::getApi('custom_fields.organizations')->getEnabledFields();
		$data_structured = App::getApi('custom_fields.util')->createDataHierarchy($org['custom_data'], $field_defs);

		$custom_fields_form = $this->get('form.factory')->createNamedBuilder('form', 'custom_fields');
		$custom_fields = App::getApi('custom_fields.organizations')->getFieldsDisplayArray($field_defs, $data_structured, $custom_fields_form);

		#------------------------------
		# Misc info needed
		#------------------------------

		$notes = App::getEntityRepository('DeskPRO:OrganizationNote')->getNotesForOrganization($org);

		$org_tickets = App::getEntityRepository('DeskPRO:Ticket')->getOrganizationTickets($org, 5);
		$org_tickets_count = App::getEntityRepository('DeskPRO:Ticket')->countTicketsForOrganization($org);

		$activity_stream = $this->em->getRepository('DeskPRO:PersonActivity')->getForOrganization($org, 10);

		// Count members
		$members_count = App::getEntityRepository('DeskPRO:Organization')->countMembersFor($org);

		return $this->render('AgentBundle:Organization:view.html.twig', array(
			'org'                => $org,
			'notes'              => $notes,
			'activity_stream'    => $activity_stream,
			'org_tickets'        => $org_tickets,
			'org_tickets_count'  => $org_tickets_count,
			'members_count'      => $members_count,
			'custom_fields'      => $custom_fields,
		));
	}


	public function ajaxGetNotesAction($organization_id)
	{
		$org = $this->getOrgOr404($organization_id);

		$per_page = min($this->in->getUint('pp'), 20);
		$page = $this->in->getUint('p');
		if (!$page) {
			$page = 1;
		}

		$start = ($page - 1) * $per_page;

		$em = App::getOrm();

		$notes = $em->createQuery("
			SELECT n, a
			FROM DeskPRO:OrganizationNote n
			LEFT JOIN n.agent a
			WHERE n.organization_id = ?1
			ORDER BY n.id DESC
		")->setParameter(1, $org['id'])
			->setMaxResults($per_page)
			->setFirstResult($start)
			->execute();

		$html = array();

		foreach ($notes as $note) {
			$html[] = $this->renderView('AgentBundle:Organization:note-li.html.twig', array('note' => $note));
		}

		$html = implode('', $html);

		return $this->createJsonResponse(array(
			'success' => true,
			'organization_id' => $org['id'],
			'notes_html' => $html,
			'page' => $page
		));
	}


	############################################################################
	# ajax-save
	############################################################################

	public function ajaxSaveAction($organization_id)
	{
		$org = $this->getOrgOr404($organization_id);

		switch ($this->in->getString('action')) {
			case 'name':
				$org['name'] = $this->in->getString('name');
				App::getOrm()->persist($org);
				App::getOrm()->flush();

				return $this->createJsonResponse(array(
					'success' => true,
					'organization_id' => $org['id'],
					'html' => htmlspecialchars($org['name'])
				));
				break;
		}
	}


	############################################################################
	# ajax-save-contact
	############################################################################

	public function ajaxSaveContactAction($organization_id)
	{
		$org = $this->getOrgOr404($organization_id);

		$type = $this->in->getString('contact_type');
		$handler = \Application\DeskPRO\Form\ContactFieldHandler\AbstractContactFieldHandler::simpleNameToClassName($type);

		$handler = new $handler();

		$contact_data = new OrganizationContactData();
		$contact_data['handler_class'] = get_class($handler);

		$post_data = isset($_POST[$handler->getSimpleName()]) ? $_POST[$handler->getSimpleName()] : array();

		foreach ($post_data as $k => $v) {
			$field_k = $k;
			if ($k != 'comment') {
				$field_k = $handler->mapNameToField($k);
			}
			if (!$field_k) continue;

			$contact_data[$field_k] = $v;
		}

		$em = App::getOrm();
		$em->beginTransaction();
		$org->addContactData($contact_data);
		$em->persist($contact_data);
		$em->flush();
		$em->commit();

		return $this->createJsonResponse(array(
			'success' => true,
			'organization_id' => $org['id'],
			'contact_html' => $this->renderView('AgentBundle:Organization:contact-section.html.twig', array('organization' => $org))
		));
	}

	############################################################################
	# ajax-save-note
	############################################################################

	public function ajaxSaveNoteAction($organization_id)
	{
		$org = $this->getOrgOr404($organization_id);

		$note_txt = $this->in->getString('note');

		$em = App::getOrm();
		$em->beginTransaction();

		$note = new OrganizationNote();
		$note['agent'] = $this->person;
		$note['organization'] = $org;
		$note['note'] = $note_txt;
		$em->persist($note);

		$em->flush();
		$em->commit();

		return $this->createJsonResponse(array(
			'success' => true,
			'organization_id' => $org['id'],
			'note_li_html' => $this->renderView('AgentBundle:Organization:note-li.html.twig', array('note' => $note))
		));
	}

	############################################################################
	# ajax-save-custom-fields
	############################################################################

	public function ajaxSaveCustomFieldsAction($organization_id)
	{
		$org = $this->getOrgOr404($organization_id);

		$field_defs = App::getApi('custom_fields.people')->getEnabledFields();
		foreach ($field_defs as $field_def) {
			foreach ($field_def->getHandler()->getDataFromForm($_POST['custom_fields']) as $info) {
				$org->setCustomData($info[0], $info[1], $info[2]);
			}
		}

		App::getOrm()->persist($org);
		App::getOrm()->flush();

		$data_structured = App::getApi('custom_fields.util')->createDataHierarchy($org['custom_data'], $field_defs);
		$custom_fields = array();
		foreach ($field_defs as $f_def) {
			$f = $f_def->getHandler()->getFormField();

			$custom_fields[] = array(
				'field_def' => $f_def,
				'title' => $f_def['title'],
				'rendered' => $data_structured[$f_def['id']] ? $f_def->getHandler()->renderHtml($data_structured[$f_def['id']]) : false
			);
		}

		return $this->createJsonResponse(array(
			'custom_fields_html' => $this->renderView('AgentBundle:Organization:custom-fields-rendered.html.twig', array('custom_fields' => $custom_fields)),
		));
	}

	############################################################################
	# ajax-save-labels
	############################################################################

	public function ajaxSaveLabelsAction($organization_id)
	{
		$org = $this->getOrgOr404($organization_id);

		$labels = $this->in->getCleanValueArray('labels', 'string', 'discard');

		$org->getLabelManager()->setLabelsArray($labels);

		App::getOrm()->persist($org);
		App::getOrm()->flush();

		return $this->createJsonResponse(array('success' => 1));
	}


	/**
	 * @return \Application\DeskPRO\Entity\Organization
	 */
	protected function getOrgOr404($organization_id)
	{
		try {
			$org = $this->em->find('DeskPRO:Organization', $organization_id);
		} catch (\Doctrine\ORM\NoResultException $e) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no organization with ID $organization_id");
		}

		return $org;
	}
}
