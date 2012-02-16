<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Form
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\CustomFields\Handler;

use Application\DeskPRO\Entity;
use Application\DeskPRO\App;


/**
 * Handles the date field
 */
class Date extends HandlerAbstract
{
	public function renderHtml(array $data = null, array $template_vars = array())
	{
		if ($data === null) return '';

		$data['value'] = date('M d, Y', $data['value']);
		parent::renderText($data, $template_vars);
	}

	public function renderText(array $data = null, array $template_vars = array())
	{
		if ($data === null) return '';

		$data['value'] = date('M d, Y', $data['value']);
		parent::renderText($data, $template_vars);
	}

	public function getFormField(array $data = null)
	{
		$setData = null;
		if ($data AND !empty($data['value'])) {
			$setData = $data['value'];
		}
		$field = App::getFormFactory()->createNamedBuilder('text', $this->getFormFieldName(), $setData, array(
			//'widget' => 'text',
			//'input' => 'timestamp',
			//'format' => 3,
			'required' => false
		));

		return $field;
	}

	function getDataFromForm(array $form_data)
	{
		$name = $this->getFormFieldName();

		$value = null;
		if (!empty($form_data[$name])) {
			return array();
			$value = $form_data[$name];
			$value = strtotime($value);
		}

		return array(
			array($this->field_def['id'], 'value', $value)
		);
	}

	public function getSearchCapabilities()
	{
		return array('is', 'not', 'gt', 'lt');
	}

	public function getSearchType()
	{
		return 'value';
	}
}
