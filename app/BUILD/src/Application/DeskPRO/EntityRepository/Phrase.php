<?php

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\Language as LanguageEntity;
use Orb\Util\Numbers;

class Phrase extends AbstractEntityRepository
{
    public function getPhraseForLanguage($phrase_name, $language = null)
    {
        if ($language === null or $language === 0) {
            return $this->getEntityManager()->createQuery('
                SELECT p
                FROM DeskPRO:Phrase p
                WHERE p.language IS NULL AND p.name = ?1
            ')->setParameters([1 => $phrase_name])->setMaxResults(1)->getOneOrNullResult();
        } else {
            return $this->getEntityManager()->createQuery('
                SELECT p
                FROM DeskPRO:Phrase p
                WHERE p.language = ?1 AND p.name = ?2
            ')->setParameters([1 => $language, 2 => $phrase_name])->setMaxResults(1)->getOneOrNullResult();
        }
    }

    /**
     * @param LanguageEntity|int $language
     *
     * @return mixed
     */
    public function getCustomPhraseNamesInLanguage($language)
    {
        if (Numbers::isInteger($language)) {
            $languageId = $language;
        } else {
            $languageId = $language->getId();
        }

        $names = $this->getEntityManager()->getConnection()->fetchColumn('
            SELECT name
            FROM phrases
            WHERE language_id = ? AND phrase IS NOT NULL
        ', [$languageId]);

        return $names;
    }

    /**
     * @param LanguageEntity|int $language
     * @param string             $group
     *
     * @return array
     */
    public function getPhrasesInGroup($language, $group, $isManaged = false)
    {
        if (Numbers::isInteger($language)) {
            $languageId = $language;
        } else {
            $languageId = $language->getId();
        }

        $parts = explode('.', $group);
        if (count($parts) == 2) {
            if ($parts[0] == $parts[1]) {
                $group = $parts[0];
            }
        }
        $phrases = $this->getEntityManager()->getConnection()->fetchAllKeyValue('
            SELECT name, COALESCE(phrase, original_phrase) AS phrase
            FROM phrases
            WHERE language_id = ? AND is_managed = ? AND groupname LIKE ?
        ', [$languageId, $isManaged, $group.'%']);

        return $phrases;
    }

    public function getLanguagePhrasesInGroup($language, $group)
    {
        return $this->_em->createQuery('
            SELECT p
            FROM DeskPRO:Phrase p INDEX BY p.name
            WHERE p.language = ?0 AND p.groupname = ?1
        ')->setParameters([$language, $group])->execute();
    }

    public function getCustomPhrases($language, $isManaged = false)
    {
        return $this->_em->createQuery("
            SELECT p
            FROM DeskPRO:Phrase p INDEX BY p.name
            WHERE p.language = ?0 AND p.phrase IS NOT NULL AND p.phrase != '' AND p.is_managed = ?1
        ")->setParameters([$language, $isManaged])->execute();
    }
}
