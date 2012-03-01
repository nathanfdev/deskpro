<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
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
