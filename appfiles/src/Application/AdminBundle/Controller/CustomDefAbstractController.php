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

		$this->tplvars['route_basename'] = $this->route_basename = 'admin_' . strtolower(str_replace('Controller', '', Util::getBaseClassname($this))) . '_';
		$this->tplvars['section'] = strtolower(str_replace(array('CustomDef', 'Controller'), '', Util::getBaseClassname($this)));
		$this->tplvars['sub_section'] = 'fields';
	}

	############################################################################
	# index
	############################################################################

	/**
	 * List fields
	 */
	public function indexAction()
	{
		$existing_fields = $this->getApi()->getFields();

		return $this->render($this->getTemplateName('index.twig.html'), array(
			'fields' => $existing_fields
		));
	}



	############################################################################
	# new-choose-type
	############################################################################

	public function newChooseTypeAction()
	{
		return $this->render($this->getTemplateName('edit-choosetype.twig.html'), array(

		));
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
			$field['handler_class'] = $this->in->getString('formfield.handler_class');
		}

		// Cant edit a specific child field; the main parent field editor must be used
		if ($field['parent']) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("$field_id is not a valid field (it has a parent)");
		}

		$renderer = new \Orb\Form\Renderer\Basic();
		$form = new \Application\AdminBundle\Form\EditField(array(
			'name' => 'formfield',
			'renderer' => $renderer,
			'event_dispatcher' => $this->get('event_dispatcher'),
			'custom_def' => $field
		));
		$form->addField(new \Orb\Form\Field\Hidden(array('name' => 'handler_class', 'data' => $field['handler_class'])));

		$admin_handler = \Application\AdminBundle\CustomField\AdminHandler\Factory::createFromFormField($field);
		$form->addField($admin_handler->buildFormGroup());

		if ($this->isPostRequest()) {
			$form->setFormData($_POST);
			if ($form->isValid()) {
				$admin_handler->saveField($form);
				return $this->redirectRoute($this->route_basename . 'edit', array('field_id' => $field['id']));
			} else {
				// TODO proper handling
				print_r($form->getErrors());
			}
		}

		$parts = explode('\\', $field['handler_class']);
		$tpl_name = 'edit-' . strtolower(array_pop($parts)) . '.twig.html';

		$vars = array_merge($admin_handler->getTemplateVars(), array(
			'field' => $field,
			'form' => $form,
		));

		return $this->render($this->getTemplateName($tpl_name), $vars);
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