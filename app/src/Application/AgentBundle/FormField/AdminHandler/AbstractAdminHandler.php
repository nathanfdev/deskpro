<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 */

namespace Application\AgentBundle\CustomField\AdminHandler;

use Application\DeskPRO\Entity\CustomDefAbstract;

use Application\DeskPRO\App;

/**
 * An admin handler that helps with building a custom field (options and the like).
 * Since each custom field is different and has its own options, each form for
 * creating or editing a field is different -- thats what these handlers do.
 *
 * @see Application\AgentBundle\Controller\FieldsController
 */
abstract class AbstractAdminHandler
{
	/**
	 * The form field definition
	 * @var Application\DeskPRO\Entity\CustomDefAbstract
	 */
	protected $fielddef;

	protected $em;

	public function __construct(CustomDefAbstract $fielddef)
	{
		$this->fielddef = $fielddef;
		$this->em = App::getOrm();

		$this->init();
	}



	/**
	 * Empty hook method
	 */
	protected function init() {}



	/**
	 * Get an array of additional fields to add to the Form object in the controller.
	 * This must always be called 'fieldtype_form'.
	 *
	 * @return \Orb\Form\Field\FieldGroup
	 */
	public function buildFormGroup()
	{
		$formgroup = new \Orb\Form\Field\FieldGroup(array('name' => 'fieldtype_form'));

		foreach ($this->buildRequiredFormFields() as $f) {
			$formgroup->addField($f);
		}


		return $formgroup;
	}



	/**
	 * Set data/options based on the current field defition.
	 *
	 * @param \Orb\Form\Field\FieldGroup $formgroup
	 */
	public function setDataOnFormGroup(\Orb\Form\Field\FieldGroup $formgroup)
	{
		$formgroup->setData($this->fielddef['data']);
	}




	/**
	 * Return an array of fields we need to add to the form.
	 *
	 * @return array
	 */
	abstract protected function buildRequiredFormFields();



	/**
	 * Gets variables we'll need to use in the template.
	 *
	 * @return array
	 */
	public function getTemplateVars()
	{
		return array();
	}



	/**
	 * Save the field
	 *
	 * @param \Orb\Form\Field\Form $form
	 */
	public function saveField(\Orb\Form\Field\Form $form)
	{
		$this->em->beginTransaction();

		$is_new = ((bool)$this->fielddef['id']);

		$this->fielddef['title'] = $form['field_properties']['title']->getData();
		$this->em->persist($this->fielddef); // need to save now 'cuz might add children, which will need the parent

		$this->handleSave($form['fieldtype_form']);

		$this->em->persist($this->fielddef); // save again incase changes made from handler

		#------------------------------
		# Save
		#------------------------------

		$this->em->flush();
		$this->em->commit();
	}



	/**
	 * Save options for the current field.
	 *
	 * @param Orb\Form\Field\FieldGroup $formgroup This is the form fragment for this type
	 */
	abstract protected function handleSave(\Orb\Form\Field\FieldGroup $formgroup);
}
