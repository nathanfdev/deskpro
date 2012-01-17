<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;

use Application\DeskPRO\Entity;
use \Doctrine\ORM\EntityRepository;
use Orb\Util\Util;

class Setting extends EntityRepository
{
	/**
	 * Update a database setting
	 *
	 * @param  $name
	 * @param  $value
	 * @return \Application\DeskPRO\Entity\Setting
	 */
	public function updateSetting($name, $value)
	{
		$setting = $this->find($name);
		if (!$setting) {
			$setting = new Entity\Setting();
			$setting['name'] = $name;
		}

		if (!is_array($value)) {
			$setting['value'] = (string)$value; // needs to be cast to a str or else 0 is ignored
		} else {
			$setting['value'] = $value;
		}

		App::getOrm()->transactional(function ($em) use ($setting) {
			$em->getConnection()->delete('settings', array('name' => $setting->name));
			$em->persist($setting);
			$em->flush();
		});

		return $setting;
	}

	/**
	 * Invalidates caches
	 */
	public function invalidateCaches()
	{
		App::getCache('common')->clean('matchingTag', array('settings'));
	}

	/**
	 * @see \Application\DeskPRO\DBAL\Logging\CacheInvalidor
	 * @param  $sql
	 * @return void
	 */
	public function invalidateFromQuery($sql)
	{
		$this->invalidateCaches();
	}
}
