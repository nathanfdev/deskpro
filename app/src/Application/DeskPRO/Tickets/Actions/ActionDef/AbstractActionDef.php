<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Actions\ActionDef;

use Application\DeskPRO\Entity\TicketActionDef;

abstract class AbstractActionDef
{
	/**
	 * @var \Application\DeskPRO\Entity\TicketActionDef
	 */
	private $action_def;


	/**
	 * @param TicketActionDef $action_def
	 */
	public function __construct(TicketActionDef $action_def)
	{
		$this->action_def = $action_def;
	}


	/**
	 * @return TicketActionDef
	 */
	public function getActionDef()
	{
		return $this->action_def;
	}


	/**
	 * @return string
	 */
	abstract function getTitle();


	/**
	 * Gets the classname for a macro action class
	 * @return string|null
	 */
	public function getTriggerActionClass()
	{
		return null;
	}


	/**
	 * Gets the classname for a macro action class
	 * @return string|null
	 */
	public function getMacroActionClass()
	{
		return null;
	}


	/**
	 * Get a string path to the option builder template
	 * @return string
	 */
	public function getActionBuilderTemplate()
	{
		return null;
	}


	/**
	 * Process the data returned from the action array.
	 * $options will be whatever info was included in the form.
	 *
	 * Return null to cancel adding the action (eg its invalid)
	 *
	 * @param array $options
	 * @return array|null
	 */
	public function processActionBuilderOptions(array $options)
	{
		return $options;
	}
}