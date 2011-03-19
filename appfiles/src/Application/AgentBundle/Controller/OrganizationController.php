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

use \Application\DeskPRO\App;

/**
 * Handles viewing and editing an org
 */
class OrganizationController extends AbstractController
{
	############################################################################
	# view
	############################################################################

	public function viewAction($org_id)
	{
		$org = $this->getOrgOr404($org_id);
		
		// Custom fields
		$field_defs = App::getApi('custom_fields.organizations')->getEnabledFields();
		$data_structured = App::getApi('custom_fields.util')->createDataHierarchy($org['custom_data'], $field_defs);

		$custom_fields_form = new \Symfony\Component\Form\CollectionField('custom_fields');
		$custom_fields = App::getApi('custom_fields.organizations')->getFieldsDisplayArray($field_defs, $data_structured, $custom_fields_form);

		#------------------------------
		# Contact fields: empty tpls
		#------------------------------

		$contact_fields_tpl = array();
		$f = new \Application\DeskPRO\Form\ContactFieldHandler\InstantMessage();
		$contact_fields_tpl['instant_message'] = $f->getFormField();

		$f = new \Application\DeskPRO\Form\ContactFieldHandler\Address();
		$contact_fields_tpl['address'] = $f->getFormField();

		$f = new \Application\DeskPRO\Form\ContactFieldHandler\Phone();
		$contact_fields_tpl['phone'] = $f->getFormField();

		return $this->render('AgentBundle:Person:view.html.twig', array(
			'org' => $org,
			'fields' => $form->getCustomFields(),
			'custom_fields' => $custom_fields,
			'contact_fields_tpl' => $contact_fields_tpl,
		));
	}



	############################################################################
	# ajax-save-contact
	############################################################################

	public function ajaxSaveContactAction($org_id)
	{
		$org = $this->getOrgOr404($org_id);
		
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


	/**
	 * @return \Application\DeskPRO\Entity\Organization
	 */
	protected function getOrgOr404($org_id)
	{
		try {
			$org = $this->em->find('DeskPRO:Organization', $org_id);
		} catch (\Doctrine\ORM\NoResultException $e) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no organization with ID $org_id");
		}

		return $org;
	}
}