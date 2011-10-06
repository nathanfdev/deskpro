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

use Application\DeskPRO\App;

use Application\DeskPRO\Entity\CustomDefAbstract;

use Orb\Util\Util;

/**
 * Abstract class for managing custom fields
 */
abstract class CustomDefAbstractController extends AbstractController
{
	const API_NAME = '';

	protected $route_basename;

	public function init()
	{
		parent::init();
		$this->setRouteBasename();
	}

	protected function setRouteBasename()
	{
		$this->route_basename = 'admin_' . strtolower(str_replace('Controller', '', Util::getBaseClassname($this))) . '_';
	}


	############################################################################
	# index
	############################################################################

	/**
	 * List fields
	 */
	public function indexAction()
	{
		$this->rememberLastPage();

		$existing_fields = $this->getApi()->getFields();

		return $this->render($this->getTemplateName('index.html.twig'), $this->getTemplateVars(array(
			'fields' => $existing_fields
		)));
	}



	############################################################################
	# new-choose-type
	############################################################################

	public function newChooseTypeAction()
	{
		$vars = array();
		return $this->render($this->getTemplateName('edit-choosetype.html.twig'), $this->getTemplateVars($vars));
	}



	############################################################################
	# /agent/person-fields/:field_id/edit                 admin_personfields_edit
	############################################################################

	public function editAction($field_id)
	{
		if ($field_id) {
			$field = $this->getFieldOr404($field_id);
		} else {
			$field = $this->createNewField();
			$field['handler_class'] = $this->in->getString('editcustomfield.handler_class');
		}

		$field_save = new \Application\AdminBundle\CustomField\FormObject($field);
		$form = $this->get('form.factory')->create(new \Application\AdminBundle\Form\EditCustomFieldType($field_save), $field_save);

		if ($this->in->getBool('process')) {
			$form->bindRequest($this->get('request'));
			if ($form->isValid()) {
				$field_save->save();
				$this->getTemplateVars(); // to get routebasename
				return $this->redirectRoute($this->route_basename . 'edit', array('field_id' => $field['id'], 'saved' => 1));
			} else {
				// TODO proper handling
				print_r($form->getErrors());
				exit;
			}
		}

		$vars = array(
			'field' => $field,
			'form' => $form->createView(),
			'saved' => $this->in->getBool('saved'),
		);

		$row_html = false;
		if ($this->in->getBool('saved')) {
			$row_html = $this->renderView('AdminBundle:CustomDefAbstract:list-row.html.twig', $this->getTemplateVars($vars));
		}

		$vars['row_html'] = $row_html;

		$parts = explode('\\', $field['handler_class']);
		$tpl_name = 'edit-' . strtolower(array_pop($parts)) . '.html.twig';

		return $this->render($this->getTemplateName($tpl_name), $this->getTemplateVars($vars));
	}


	############################################################################
	# set-enabled
	############################################################################

	public function setEnabledAction($field_id)
	{
		$field = $this->getFieldOr404($field_id);
		$field->is_enabled = $this->in->getBool('is_enabled');

		$this->em->transactional(function($em) use ($field) {
			$em->persist($field);
			$em->flush();
		});

		return $this->createJsonResponse(array('success' => true));
	}



	############################################################################

	/**
	 * @return Application\DeskPRO\Entity\CustomDefAbstract
	 */
	protected function getFieldOr404($field_id)
	{
		try {
			$field = $this->em->find($this->getApi()->getEntityName(), $field_id);
		} catch (\Doctrine\ORM\NoResultException $e) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no field with ID $field_id");
		}

		return $field;
	}



	/**
	 * Create a new custom field def object.
	 *
	 * @return CustomFieldDef
	 */
	protected function createNewField()
	{
		$classname = $this->getApi()->getEntityClassname();

		$field = new $classname();

		return $field;
	}


	/**
	 * Get template vars used on all pages.
	 *
	 * @param array $merge_vars Merge with these variables
	 * @return array
	 */
	public function getTemplateVars(array $merge_vars = null)
	{
		$template_vars = array();
		$template_vars['route_basename'] = $this->route_basename = 'admin_' . strtolower(str_replace('Controller', '', Util::getBaseClassname($this))) . '_';
		$template_vars['section'] = strtolower(str_replace(array('CustomDef', 'Controller'), '', Util::getBaseClassname($this)));
		$template_vars['sub_section'] = 'fields';

		if ($merge_vars) {
			$template_vars = array_merge($template_vars, $merge_vars);
		}

		return $template_vars;
	}



	/**
	 * Get the proper path for a template with this controller.
	 *
	 * @param string $tpl
	 */
	public function getTemplateName($tpl)
	{
		$name = 'AdminBundle:' . str_replace('Controller', '', Util::getBaseClassname($this)) . ':' . $tpl;

		if (!$this->tpl->exists($name)) {
			$name = 'AdminBundle:CustomDefAbstract:' . $tpl;
		}

		return $name;
	}



	/**
	 * Get the handler for working with custom fields.
	 *
	 * @return Application\DeskPRO\CustomFields\AbstractFields
	 */
	public function getApi()
	{
		return App::getApi(static::API_NAME);
	}
}
