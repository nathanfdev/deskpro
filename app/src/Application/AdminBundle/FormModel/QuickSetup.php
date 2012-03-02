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
 * @subpackage AdminBundle
 */

namespace Application\AdminBundle\FormModel;

use Application\DeskPRO\App;

use Orb\Util\Arrays;

class QuickSetup
{
	public $default_from_email;
	public $site_url;
	public $site_name;
	public $deskpro_url;
	public $deskpro_name;
	public $timezone;
	public $portal_enabled;

	protected $_settings_map = array(
		'default_from_email'       => 'core.default_from_email',
		'site_url'                 => 'core.site_url',
		'site_name'                => 'core.site_name',
		'deskpro_name'             => 'core.deskpro_name',
		'deskpro_url'              => 'core.deskpro_url',
		'timezone'                 => 'core.default_timezone',
		'portal_enabled'           => 'user.portal_enabled',
	);

	public function __construct()
	{
		foreach ($this->_settings_map as $prop => $key) {
			$v = App::getSetting($key);
			if ($v == '1') $v = true; elseif ($v == '0') $v = false;
			$this->$prop = $v;
		}
		$this->deskpro_url = '';
	}

	public function getErrors()
	{
		$errors = array();
		if (!$this->default_from_email || !\Orb\Validator\StringEmail::isValueValid($this->default_from_email)) {
			$errors['invalid_default_from_email'] = true;
		}

		if (!$this->deskpro_url) {
			$errors['invalid_deskpro_url'] = true;
		}
		if (!$this->deskpro_name) {
			$errors['invalid_deskpro_name'] = true;
		}

		if (!$this->timezone) {
			$errors['invalid_timezone'] = true;
		}

		return $errors;
	}

	public function save()
	{
		$em = App::getOrm();
		$em->getConnection()->beginTransaction();

		try {
			foreach ($this->_settings_map as $prop => $k) {
				App::getEntityRepository('DeskPRO:Setting')->updateSetting($k, $this->$prop);
			}

			App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.setup_initial', '1');

			$em->getConnection()->commit();
		} catch (\Exception $e) {
			$em->getConnection()->rollback();
			throw $e;
		}
	}
}
