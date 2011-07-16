<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage PageDisplay
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\PageDisplay\Item\Portal;

use Application\DeskPRO\Entity\PortalPageDisplay;

interface CacheableItem
{
	/**
	 * If the section is cacheable (ie given options etc), return true or an array of
	 * options:
	 * - tags
	 * - lifetime
	 * - user_indifferent: When true, this signifies the cache doesnt change for usergroups,
	 *                     so a generic cache will be used for everyone (saves space etc).
	 * 
	 * @return mixed
	 */
	public function getCacheOptions();
}