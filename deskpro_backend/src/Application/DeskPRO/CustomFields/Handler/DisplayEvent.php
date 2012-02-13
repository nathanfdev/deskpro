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
use Application\DeskPRO\EventDispatcher\FilterPluginInterface;

class DisplayEvent extends DataEvent implements FilterPluginInterface
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

	/**
	 * @param Plugin $plugins
	 * @return bool
	 */
	public function filterPlugins($plugin)
	{
		if (isset($plugin['event_options']['field_table'])) {
			if ($plugin['event_options']['field_table'] != $this->field_def->getTableName()) {
				return false;
			}
		}

		if (isset($plugin['event_options']['field_id'])) {
			if ($plugin['event_options']['field_id'] != $this->field_def['id']) {
				return false;
			}
		}

		return true;
	}
}