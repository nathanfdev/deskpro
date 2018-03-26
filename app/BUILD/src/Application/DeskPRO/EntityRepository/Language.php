<?php

/**
 * DeskPRO.
 *
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
        return $this->_em->createQuery('SELECT COUNT(l) FROM DeskPRO:Language l WHERE l.has_user = true ORDER BY l.title ASC')->getSingleScalarResult();
    }

    public function getPortalLanguages()
    {
        return $this->_em->createQuery('SELECT l FROM DeskPRO:Language l WHERE l.has_user = true ORDER BY l.title ASC')->getResult();
    }

    public function getDefaultPortalLanguage()
    {
        return $this->_em->createQuery('SELECT COUNT(l) FROM DeskPRO:Language l WHERE l.has_user = true ORDER BY l.title ASC')->getSingleScalarResult();
    }

    /**
     * @return array
     */
    public function getTitles($for_ids = null)
    {
        if ($this->lang_titles === null) {
            $db                = $this->getEntityManager()->getConnection();
            $this->lang_titles = $db->fetchAllKeyValue('
                SELECT id, title
                FROM languages
                ORDER BY title ASC
            ');
        }

        if (!$for_ids) {
            return $this->lang_titles;
        }

        $ret = [];
        foreach ((array) $for_ids as $id) {
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
     *
     * @throws \Exception
     */
    public function installAll(LangPackInfo $langpacks)
    {
        $em = $this->_em;
        $db = $em->getConnection();

        $installed = $db->fetchAllCol('
            SELECT sys_name
            FROM languages
        ');

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

    public function getByTitle($title)
    {
        return $this->getEntityManager()->createQuery('
				SELECT l FROM DeskPRO:Language l
				WHERE l.sys_name = :title OR LOWER(l.title) = :title
			')->setParameter('title', mb_strtolower(trim($title)))->getOneOrNullResult();
    }

    /**
     * @param $langCode
     *
     * @return null|\Application\DeskPRO\Entity\Language
     */
    public function getForLangCode($langCode)
    {
        if (!$langCode || !is_string($langCode)) {
            return;
        }
        if (!strlen($langCode) == 2) {
            $langCode = substr($langCode, 0, 2);
        }

        if ($langCode == 'en') {
            $langCode = 'en_US';
        }

        if ($langCode == 'es') {
            $langCode = 'ES_es';
        }

        $r = $this->findOneBy(['locale' => $langCode]);

        return $r;
    }
}
