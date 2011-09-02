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

		$usergroup_names = App::getEntityRepository('DeskPRO:Usergroup')->getUsergroupNames();
		$org_usergroups = $org->usergroups;

		$contact_data = array();
		foreach ($org->contact_data as $cd) {
			if (!isset($contact_data[$cd->contact_type])) {
				$contact_data[$cd->contact_type] = array();
			}
			$contact_data[$cd->contact_type][] = $cd->getTemplateVars();
		}

		return $this->render('AgentBundle:Organization:view.html.twig', array(
			'org'                => $org,
			'contact_data'       => $contact_data,
			'org_usergroups'     => $org_usergroups,
			'usergroup_names'    => $usergroup_names,
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

		$this->em->beginTransaction();
		$data = array(
			'success' => true
		);

		switch ($this->in->getString('action')) {
			case 'name':
				if ($this->in->getString('name')) {
					$org->name = $this->in->getString('name');
					$this->em->persist($org);
				}
				break;

			case 'delete-picture':
				$org->picture_blob = null;
				$this->em->persist($org);
				break;

			case 'set-picture':
				$blob = App::findEntity('DeskPRO:Blob', $this->in->getUint('blob_id'));
				if ($blob) {
					$org->picture_blob = $blob;
					$this->em->persist($org);
				}
				break;

			default:
				return $this->createJsonResponse(array('error' => true, 'message' => 'Unknown action'));
				break;
		}

		$this->em->flush();
		$this->em->commit();

		return $this->createJsonResponse($data);
	}

	public function ajaxSaveCustomFieldsAction($organization_id)
	{
		$org = $this->getOrgOr404($organization_id);

		$org_field_defs = App::getApi('custom_fields.organizations')->getEnabledFields();

		if (!empty($_POST['custom_fields'])) {
			foreach ($org_field_defs as $field_def) {
				foreach ($field_def->getHandler()->getDataFromForm($_POST['custom_fields']) as $info) {
					$org->setCustomData($info[0], $info[1], $info[2]);
				}
			}

			App::getOrm()->persist($org);
			App::getOrm()->flush();
		}

		// Custom fields
		$org_data_structured = App::getApi('custom_fields.util')->createDataHierarchy($org['custom_data'], $org_field_defs);

		// We use this fieldgroup so the form names are part of custom_fields array: custom_fields[field_1] etc
		// So dont remove it even though it looks like it's not used! :-)
		$custom_fields_form = $this->get('form.factory')->createNamedBuilder('form', 'custom_fields');
		$custom_fields = App::getApi('custom_fields.people')->getFieldsDisplayArray($org_field_defs, $org_data_structured, $custom_fields_form);

		// Usergroups
		$db = App::getDb();
		$db->delete('organization2usergroups', array('organization_id' => $org['id']));

		$usergroups = $this->in->getCleanValueArray('usergroups', 'uint', 'discard');
		foreach ($usergroups as $u) {
			$db->insert('organization2usergroups', array(
				'organization_id' => $org['id'],
				'usergroup_id' => $u
			));
		}

		$usergroup_names = App::getEntityRepository('DeskPRO:Usergroup')->getUsergroupNames();
		$org_usergroups = App::getEntityRepository('DeskPRO:Usergroup')->getByIds($usergroups);

		return $this->render('AgentBundle:Organization:view-customfields-rendered-rows.html.twig', array(
			'org'             => $org,
			'custom_fields'   => $custom_fields,
			'usergroup_names' => $usergroup_names,
			'org_usergroups'  => $org_usergroups,
		));
	}

	public function changePictureOverlayAction($organization_id)
	{
		$org = $this->getOrgOr404($organization_id);

		return $this->render('AgentBundle:Organization:change-person-picture.html.twig', array(
			'org' => $org
		));
	}

	public function saveContactDataAction($organization_id)
	{
		$org = $this->getOrgOr404($organization_id);

		$this->em->beginTransaction();

		// Adding contact data
		foreach ($this->in->getCleanValueArray('new_contact_data') as $type => $inputs) {
			foreach ($inputs as $input) {
				try {
					$contact_data = new OrganizationContactData();
					$contact_data->contact_type = $type;
					$contact_data->applyFormData($input);

					$contact_data->organization = $org;

					$this->em->persist($contact_data);
					$org->contact_data->add($contact_data);
				} catch (\Exception $e) {
					throw $e;
				}
			}
		}

		// Editing values
		foreach ($this->in->getCleanValueArray('new_contact_data') as $id => $input) {
			if (!isset($org->contact_data[$id])) {
				continue;
			}

			$org->contact_data[$id]->applyFormData($input);
			$this->em->persist($org->contact_data[$id]);
		}

		// Removing values
		foreach ($this->in->getCleanValueArray('remove_contact_data', 'uint') as $id) {
			if (isset($org->contact_data[$id])) {
				$this->em->remove($org->contact_data[$id]);
				$org->contact_data->remove($id);
			}
		}

		$this->em->flush();
		$this->em->commit();

		$contact_data = array();
		foreach ($org->contact_data as $cd) {
			if (!isset($contact_data[$cd->contact_type])) {
				$contact_data[$cd->contact_type] = array();
			}
			$contact_data[$cd->contact_type][] = $cd->getTemplateVars();
		}

		$display_html = $this->renderView('AgentBundle:Organization:view-contact-display.html.twig', array(
			'org' => $org,
			'contact_data' => $contact_data,
		));
		$editor_overlay_html = $this->renderView('AgentBundle:Organization:contact-overlay.html.twig', array(
			'org' => $org,
			'contact_data' => $contact_data,
		));

		return $this->createJsonResponse(array(
			'success' => 1,
			'display_html' => $display_html,
			'editor_overlay_html' => $editor_overlay_html
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
