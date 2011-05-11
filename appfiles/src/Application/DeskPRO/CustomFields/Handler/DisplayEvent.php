<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage CustomFields
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\CustomFields\Handler;

use Application\DeskPRO\EventDispatcher\DataEvent;

class DisplayEvent extends DataEvent
{
	protected $field_def;

	public function __construct($field_def, array $data = array())
	{
		parent::__construct($data);
		$this->field_def = $field_def;
	}

	public function getField()
	{
		return $this->field_def;
	}
}