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

class SettingsStep extends AbstractDeskpro3Step
{
	/**
	 * @var \Application\DeskPRO\Import\Importer\Deskpro3Importer
	 */
	protected $importer;

	public static function getTitle()
	{
		return 'Import Settings';
	}

	public function run($page = 1)
	{
		$dp3_settings = $this->getOldDb()->fetchAllKeyValue("SELECT name, value FROM settings");

		$timezone = \Orb\Util\Dates::timezoneOffsetToName($dp3_settings['timezone'], (bool)((int)$dp3_settings['dst']));
		if (!$timezone) {
			$timezone = 'UTC';
		}

		$save_settings = array(
			'core.default_from_email' => $dp3_settings['email_from'],
			'core.site_url'           => $dp3_settings['site_url'],
			'core.site_name'          => $dp3_settings['site_name'],
			'core.deskpro_name'       => $dp3_settings['site_name'],
			'core.deskpro_url'        => $dp3_settings['helpdesk_url'],
			'core.default_timezone'   => $timezone,
			'user.portal_enabled'     => 1,
			'core.setup_initial'      => 11
		);

		$this->getDb()->beginTransaction();
		try {
			foreach ($save_settings as $sk => $sv) {
				list($sg,) = explode('.', $sk, 2);
				$this->getDb()->replace('settings', array(
					'name'       => $sk,
					'groupname'  => $sg,
					'value'      => $sv,
					'created_at' => date('Y-m-d H:i:s'),
					'updated_at' => date('Y-m-d H:i:s')
				));
			}

			$this->getDb()->commit();
		} catch (\Exception $e) {
			$this->getDb()->rollback();
			throw $e;
		}
	}
}
