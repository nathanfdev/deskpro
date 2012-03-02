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
		$setting = $this->findOneBy(array('name' => $name));
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
