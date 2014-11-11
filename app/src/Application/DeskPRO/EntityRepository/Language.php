<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
use Application\DeskPRO\Languages\LangPackInfo;

class Language extends AbstractEntityRepository
{
	/** @var string|null */
	protected $lang_titles = null;
	/** @var \Application\DeskPRO\Entity\Language|null */
	protected $default_lang = null;


	public function countPortalLanguages()
	{
		return $this->_em->createQuery('SELECT COUNT(l) FROM DeskPRO:Language l WHERE l.has_user = true')->getSingleScalarResult();
	}

	public function getDefaultPortalLanguage()
	{
		return $this->_em->createQuery('SELECT COUNT(l) FROM DeskPRO:Language l WHERE l.has_user = true')->getSingleScalarResult();
	}

	/**
	 * @return array
	 */
	public function getTitles($for_ids = null)
	{
		if ($this->lang_titles === null) {
            $db = $this->getEntityManager()->getConnection();
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
        foreach ((array)$for_ids as $id) {
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


	/**
	 * Install all lang packs form $langpacks that arent already installed.
	 *
	 * @param \Application\DeskPRO\Languages\LangPackInfo $langpacks
	 * @throws \Exception
	 */
	public function installAll(LangPackInfo $langpacks)
	{
		$em = $this->_em;
		$db = $em->getConnection();

		$installed = $db->fetchAllCol("
			SELECT sys_name
			FROM languages
		");

		$installed = array_flip($installed);

		foreach ($langpacks->getLangIds() as $id) {
			if (isset($installed[$id])) {
				continue;
			}

			$lang = $langpacks->newLanguageEntity($id);
			$em->persist($lang);
		}

		$db->beginTransaction();
		try {
			$em->flush();
			$db->commit();
		} catch (\Exception $e) {
			$db->rollback();
			throw $e;
		}
	}

	public function getForLangCode($lang_code)
	{
		if (!strlen($lang_code) == 2) {
			$lang_code = substr($lang_code, 0, 2);
		}

		if ($lang_code == 'en') {
			$lang_code = 'en_US';
		}

		if ($lang_code == 'es') {
			$lang_code = 'ES_es';
		}

		$r = $this->findOneBy(array('locale' => $lang_code));
//		var_dump($lang_code, $r);exit;
		return $r;
	}
}
