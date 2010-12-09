<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Usersources
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\AgentBundle\Usersource\AdminHandler;

use \Application\DeskPRO\Entity\Usersource;

use \Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * The setup classes handle showing the user a wizard, and then taking input and
 * transforming it (if necessary) into adapter options.
 */
abstract class AbstractAdminHandler
{
	protected $usersource;

	/**
	 * Entity manager
	 * @var Doctrine\ORM\EntityManager
	 */
	protected $em;

	public function __construct(Usersource $usersource)
	{
		$this->usersource = $usersource;
		$this->em = App::getOrm();

		$this->init();
	}



	/**
	 * Empty hook method
	 */
	protected function init() {}



	/**
	 * Get an array of additional fields to add to the Form object in the controller.
	 * This must always be called 'type_form'.
	 *
	 * @return \Orb\Form\Field\FieldGroup
	 */
	public function buildFormGroup()
	{
		$formgroup = new \Orb\Form\Field\FieldGroup(array('name' => 'type_form'));

		foreach ($this->buildFormFields() as $f) {
			$formgroup->addField($f);
		}

		$this->setFormDataFromUsersource($formgroup);

		return $formgroup;
	}



	/**
	 * Return an array of fields we need to add to the form.
	 *
	 * @return array
	 */
	abstract protected function buildFormFields();



	/**
	 * Set data/options based on the current field defition.
	 *
	 * @param \Orb\Form\Field\FieldGroup $formgroup
	 */
	public function setFormDataFromUsersource(\Orb\Form\Field\FieldGroup $formgroup)
	{
		$formgroup->setData($this->usersource['options']);
	}



	/**
	 * This renders the HTML for a particular field types options. Standard options
	 * always exist, like a title, but the rest is per-custom field type.
	 *
	 * @return string
	 */
	public function renderFormPartial($controller, $form)
	{
		$classname = get_class($this);
		$parts = explode('\\', $classname);
		$basename = strtolower(array_pop($parts));

		$tplname = 'AgentBundle:Usersources:edit-form-.twig' . $basename;

		return $controller->renderView($tplname, array(
			'usersource' => $this->usersource,
			'form' => $form['type_form'],
			'full_form' => $form
		));
	}



	/**
	 * Save the usersource
	 *
	 * @param \Orb\Form\Field\Form $form
	 */
	public function saveUsersource(\Orb\Form\Field\Form $form)
	{
		$this->em->beginTransaction();

		$is_new = ((bool)$this->usersource['id']);

		$this->usersource['title'] = $form['basic_properties']['title']->getData();

		$this->handleSave($form['type_form']);

		$this->em->persist($this->usersource);

		#------------------------------
		# Save
		#------------------------------

		$this->em->flush();
		$this->em->commit();
	}



	/**
	 * Save options
	 *
	 * @param Orb\Form\Field\FieldGroup $formgroup This is the form fragment for this type
	 */
	protected function handleSave(\Orb\Form\Field\FieldGroup $formgroup)
	{
		$this->usersource['options'] = $formgroup->getData();
	}
}