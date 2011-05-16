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

namespace Application\AdminBundle\CustomField\AdminHandler;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\App;

use Symfony\Component\Form\FormBuilder;

/**
 * An admin handler that helps with building a custom field (options and the like).
 * Since each custom field is different and has its own options, each form for
 * creating or editing a field is different -- thats what these handlers do.
 *
 * // TODO: A lot of this saving stuff needs to be moved a DeskPRO\ helper class
 * and extended to the API.
 *
 * @see Application\AdminBundle\Controller\FieldsController
 */
abstract class AbstractAdminHandler
{
	/**
	 * The form field definition
	 * @var Application\DeskPRO\Entity\CustomDefAbstract
	 */
	protected $custom_def;

	protected $em;

	public function __construct(CustomDefAbstract $custom_def)
	{
		$this->custom_def = $custom_def;
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
	 */
	abstract public function buildForm(FormBuilder $builder, array $options, $formtype);

	
	/**
	 * Called before saving the field
	 */
	public function preSave($field_save)
	{
	}

	/**
	 * Called after saving the field
	 */
	public function postSave($field_save)
	{
	}
}