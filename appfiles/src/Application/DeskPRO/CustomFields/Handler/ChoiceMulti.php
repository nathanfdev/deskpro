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
 * Handles the choice field
 */
class ChoiceMulti extends Choice
{
	protected $multiple = true;
	protected $expanded = true;

	public function getSearchCapabilities()
	{
		return array('contains', 'not_contains');
	}

	public function getSearchType()
	{
		return 'id';
	}
}
