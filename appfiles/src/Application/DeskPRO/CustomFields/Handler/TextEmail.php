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
class TextEmail extends Text
{
	public function renderHtml(array $data)
	{
		$html = '<a href="mailto:' . htmlspecialchars($data['value']) . '">' . htmlspecialchars($data['value']) . '</a>';
		return $html;
	}
}