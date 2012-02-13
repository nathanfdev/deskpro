<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Tickets\TicketChangeInspector\LogActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

class CustomField implements LogActionInterface
{
	protected $old;
	protected $new;
	protected $field_def;

	public function __construct($old, $new)
	{
		$this->field_def = $old['field_def'];

		$this->old = $old['value'];
		$this->new = $new['value'];
	}

	public function getLogName()
	{
		return 'changed_custom_field';
	}

	public function getLogDetails()
	{
		return array(
			'value_before' => $this->old,
			'value_after' => $this->new,

			'field_id' => $this->field_def->id,
			'field_name' => $this->field_def->title
		);
	}

	public function getEventType()
	{
		return 'property';
	}
}
