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

namespace Application\AdminBundle\CustomField;

use Application\DeskPRO\App;
use Orb\Util\Util;

class FormObject
{
	public $title = '';
	public $options = array();

	public $handler_class;

	public $choices = array();
	public $choices_flat = '';

	public $custom_display_html = '';
	public $custom_form_html = '';

	protected $display_tempalte_path;
	protected $form_tempalte_path;

	protected $field_def;
	protected $admin_handler;

	public function __construct($field_def)
	{
		$this->field_def = $field_def;
		$this->handler_class = $field_def['handler_class'];
		$this->title = $field_def['title'];
		$this->options = $field_def['options'];

		$this->display_template_path = 'DeskPRO:' . $this->field_def->getTableName() . ':render-field_' . $this->field_def['id'] . '.html.twig';
		$this->form_template_path = 'DeskPRO:' . $this->field_def->getTableName() . ':form-field_' . $this->field_def['id'] . '.html.twig';

		$this->admin_handler = $this->initAdminHandler();

		if ($field_def['has_form_template']) {
			$tpl = App::getEntityRepository('DeskPRO:Template')->getTemplateForStyle($this->form_template_path);
			if ($tpl) {
				$this->custom_form_html = $tpl['template'];
			}
		}
		if ($field_def['has_display_template']) {
			$tpl = App::getEntityRepository('DeskPRO:Template')->getTemplateForStyle($this->display_template_path);
			if ($tpl) {
				$this->custom_display_html = $tpl['template'];
			}
		}

		$val = array();
		foreach ($this->field_def['children'] as $child) {
			if ($child['handler_class']) continue; // would be "other"
			$val[] = $child['title'];
		}
		if ($val) {
			$this->choices = $val;
			$this->choices_flat = implode("\n", $val);
		}
	}

	protected function initAdminHandler()
	{
		$handler = null;

		$base_classname = Util::getBaseClassname($this->handler_class);

		// The handler classname should be the same basename, but different namespace
		$handler_classname = 'Application\\AdminBundle\\CustomField\\AdminHandler\\' . $base_classname;

		if (class_exists($handler_classname)) {
			$handler = new $handler_classname($this->field_def);
		}

		if (!$handler) {
			return null;
		}

		return $handler;
	}

	public function getAdminHandler()
	{
		return $this->admin_handler;
	}

	public function save()
	{
		$field_def = $this->field_def;
		$field_def['title'] = $this->title;
		$field_def['options'] = $this->options;

		App::getOrm()->beginTransaction();

		$admin_handler = $this->getAdminHandler();
		if ($admin_handler) $admin_handler->preSave($this);

		App::getOrm()->persist($field_def);
		App::getOrm()->flush();

		$tpl = App::getEntityRepository('DeskPRO:Template')->getTemplateForStyle($this->form_template_path);
		if ($this->custom_form_html) {
			if (!$tpl) {
				$tpl = new \Application\DeskPRO\Entity\Template();
				$tpl['path'] = $this->form_tempalte_path;
				$tpl['template'] = $this->custom_form_html;
			}
			$tpl['template_compiled'] = '';
			$tpl['style'] = null;

			App::getOrm()->persist($tpl);
			$field_def['has_form_template'] = true;
		} else {
			$field_def['has_form_template'] = false;
			if ($tpl) {
				App::getOrm()->remove($tpl);
			}
		}

		$tpl = App::getEntityRepository('DeskPRO:Template')->getTemplateForStyle($this->display_template_path);
		if ($this->custom_form_html) {
			if (!$tpl) {
				$tpl = new \Application\DeskPRO\Entity\Template();
				$tpl['path'] = $this->display_template_path;
				$tpl['template'] = $this->custom_display_html;
			}
			$tpl['template_compiled'] = '';
			$tpl['style'] = null;

			App::getOrm()->persist($tpl);
			$field_def['has_display_template'] = true;
		} else {
			$field_def['has_display_template'] = false;
			if ($tpl) {
				App::getOrm()->remove($tpl);
			}
		}

		if ($admin_handler) $admin_handler->postSave($this);

		App::getOrm()->flush();
		App::getOrm()->commit();
	}
}