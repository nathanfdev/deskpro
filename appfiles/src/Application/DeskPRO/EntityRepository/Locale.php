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

use \Application\DeskPRO\App;

use \Doctrine\ORM\EntityRepository;

class Locale extends EntityRepository
{
	protected $locale_titles = null;

	/**
	 * @return array
	 */
	public function getLocaleNames($for_ids = null)
	{
		if ($this->locale_titles === null) {
            $db = App::getDb();
            $this->locale_titles = $db->fetchAllKeyValue("
                SELECT id, title
                FROM locales
                ORDER BY title ASC
            ");
        }

        if (!$for_ids) {
            return $this->locale_titles;
        }

        $ret = array();
        foreach ($for_ids as $id) {
            $ret[$id] = $this->locale_titles[$id];
        }

        return $ret;
	}
}