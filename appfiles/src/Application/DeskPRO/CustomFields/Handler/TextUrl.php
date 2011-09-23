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

/**
 * Handles the text field
 */
class TextUrl extends Text
{
	public function renderHtml(array $data)
	{
		$snipped = preg_replace('#^https?://#', '', $data['value']);
		$html = '<a href="' . htmlspecialchars($data['value']) . '" target="_blank">' . htmlspecialchars($snipped) . '</a>';
		return $html;
	}
}
