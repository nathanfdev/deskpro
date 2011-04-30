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
		$setData = null;
		if ($data AND !empty($data['value'])) {
			$setData = $data['value'];
		}
		$field = App::getFormFactory()->createNamedBuilder('textarea', $this->getFormFieldName(), $setData, array('required' => false));

		return $field;
	}
}