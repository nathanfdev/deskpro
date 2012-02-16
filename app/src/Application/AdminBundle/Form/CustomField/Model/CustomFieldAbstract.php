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

namespace Application\AdminBundle\Form\CustomField\Model;

use Application\DeskPRO\Entity\CustomDefAbstract;

use Application\DeskPRO\App;

abstract class CustomFieldAbstract
{
	public $title;
	public $handler_class;

	public $required = false;
	public $agent_required = false;

	public $custom_css_classname = '';
	public $custom_css = '';
	public $validation_type = '';
	public $agent_validation_type = '';

	protected $_field = null;
	protected $_is_new = false;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $_em;

	public function __construct(CustomDefAbstract $field)
	{
		$this->_field = $field;
		$this->title = $field->title;
		$this->handler_class = $field->handler_class;
		$this->custom_css_classname = $field->getOption('custom_css_classname');

		if ($field->getOption('required')) {
			$this->required = true;
		}

		if (!$this->_field->id) {
			$this->_is_new = true;
		}

		$this->_em = App::getOrm();

		$this->init();
	}

	protected function init() {}

	public function isNewField()
	{
		return $this->_is_new;
	}

	public function save()
	{
		$field = $this->_field;

		$field->title = $this->title;
		if ($this->isNewField()) {
			$field->handler_class = $this->handler_class;
		}

		$field->setOption('custom_css_classname', $this->custom_css_classname);

		$this->setFieldProperties();

		$this->_em->beginTransaction();
		try {
			$this->_em->persist($field);
			$this->_em->flush();

			$this->saveAdditional();
			$this->_em->flush();

			$this->_em->commit();
		} catch (\Exception $e) {
			$this->_em->rollback();
			throw $e;
		}
	}

	protected function setFieldProperties() {}
	protected function saveAdditional() {}
}
