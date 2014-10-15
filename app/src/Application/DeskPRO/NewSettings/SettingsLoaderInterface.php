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
 * @subpackage Settings
 */

namespace Application\DeskPRO\NewSettings;

/**
 * Knows how to load some representation of settings from somewhere
 *
 * @package Application\DeskPRO\Settings
 */
interface SettingsLoaderInterface
{
	/**
	 * Must return an array of it's representation of the settings. SHOULD use a cache as this method may be called
	 * many times in a single request, and the method contract requires the ability to force a reload of the data,
	 * implying the same data is returned each time $force === false.
	 *
	 * @param bool $force true if cache should be invalidated and forced to refresh the data
	 * @return array
	 */
	public function load($force = false);
}
 