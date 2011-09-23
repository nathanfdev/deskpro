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

use \Doctrine\ORM\EntityRepository;

class Language extends EntityRepository
{
	protected $lang_titles = null;
	protected $default_lang = null;

	/**
	 * @return array
	 */
	public function getTitles($for_ids = null)
	{
		if ($this->lang_titles === null) {
            $db = App::getDb();
            $this->lang_titles = $db->fetchAllKeyValue("
                SELECT id, title
                FROM languages
                ORDER BY title ASC
            ");
        }

        if (!$for_ids) {
            return $this->lang_titles;
        }

        $ret = array();
        foreach ($for_ids as $id) {
            $ret[$id] = $this->lang_titles[$id];
        }

        return $ret;
	}



	/**
	 * @return \Application\DeskPRO\Entity\Language
	 */
	public function getDefault()
	{
		if ($this->default_lang !== null) {
			return $this->default_lang;
		}

		$lang_id = App::getSetting('core.default_language_id');
		if (!$lang_id) {
			$lang_id = 1;
		}

		$this->default_lang = $this->find($lang_id);

		return $this->default_lang;
	}
}
