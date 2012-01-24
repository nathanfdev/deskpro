<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Import
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Import\Importer\Step\Deskpro3;

use Application\DeskPRO\Import\Importer\Step\AbstractStep;

class SettingsStep extends AbstractStep
{
	/**
	 * @var \Application\DeskPRO\Import\Importer\Deskpro3Importer
	 */
	protected $importer;

	public function getTitle()
	{
		return 'Import Settings';
	}

	public function run()
	{

	}
}
