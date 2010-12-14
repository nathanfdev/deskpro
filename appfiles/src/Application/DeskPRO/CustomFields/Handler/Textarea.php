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

use \Application\DeskPRO\Entity;

/**
 * Handles the text field
 */
class Textarea extends Text
{
	public function renderHtml(array $data)
	{
		return nl2br(htmlspecialchars($this->renderText($data)));
	}

	public function getFormField(array $data = null)
	{
		$field = new \Symfony\Component\Form\TextareaField($this->getFormFieldName());

		if ($data AND !empty($data['value'])) {
			$field->setData($data['value']);
		}

		return $field;
	}
}