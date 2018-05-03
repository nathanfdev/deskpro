<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Languages;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Language;
use Doctrine\ORM\EntityManager;

class LanguageInstaller
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\Entity\Language
     */
    protected $upgrade_language;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * This will insert the lang pack into an existing language,
     * upgrading it instead of installing a brand new one.
     *
     * @param \Application\DeskPRO\Entity\Language $language
     */
    public function setUpgradeLanguage(Language $language)
    {
        $this->upgrade_language = $language;
    }

    /**
     * Install a language pack from a pack file located at $pack_path.
     *
     * @param string $pack_path
     */
    public function installFromPackFilePath($pack_path)
    {
        $pack_file = LanguagePackFile::newFromFile($pack_path);

        return $this->installPack($pack_file->getPack());
    }

    /**
     * Install a language pack from a pack file loaded into a string.
     *
     * @param string $pack_string
     */
    public function installFromPackFileString($pack_string)
    {
        $pack_file = LanguagePackFile::newFromString($pack_string);

        return $this->installPack($pack_file->getPack());
    }

    /**
     * Install a language pack from an already loaded LanguagePackFile.
     *
     * @param \Application\DeskPRO\Languages\LanguagePackFile $pack_file
     */
    public function installFromPackFile(LanguagePackFile $pack_file)
    {
        return $this->installPack($pack_file->getPack());
    }

    /**
     * Install a new language pack.
     *
     * @param \Application\DeskPRO\Languages\LanguagePack $pack
     *
     * @throws \Exception
     *
     * @return \Application\DeskPRO\Entity\Language
     */
    public function installPack(LanguagePack $pack)
    {
        $this->em->getConnection()->beginTransaction();
        try {
            if ($this->upgrade_language) {
                $lang            = $this->upgrade_language;
                $lang->sys_name  = $pack->sys_name;
                $lang->lang_code = $pack->lang_code;

                // Delete all phrases that arent customized
                $this->em->getConnection()->executeUpdate("
                    DELETE FROM phrases
                    WHERE language_id = ? AND phrase IS NULL OR phrase = ''
                ", [$lang->getId()]);

                // Figure out obsolete phrases to delete
                $custom_phrase_ids = $this->em->getConnection()->fetchAllCol('
                    SELECT name FROM phrases
                    WHERE language_id = ?
                ', [$lang->getId()]);

                $delete_ids = [];

                foreach ($custom_phrase_ids as $phrase_id) {
                    if (!isset($pack->phrases[$phrase_id])) {
                        $delete_ids[] = $phrase_id;
                    }
                }

                if ($delete_ids) {
                    $this->em->getConnection()->executeUpdate('
                        DELETE FROM phrases
                        WHERE language_id = ? AND name IN (?)
                    ', [$lang->getId(), $delete_ids], [\PDO::PARAM_INT, Connection::PARAM_INT_ARRAY]);
                }
            } else {
                $lang            = new Language();
                $lang->title     = $pack->title;
                $lang->locale    = $pack->locale;
                $lang->sys_name  = $pack->sys_name;
                $lang->lang_code = $pack->lang_code;

                $this->em->persist($lang);
                $this->em->flush();
            }

            $lang_id    = $lang->getId();
            $created_at = date('Y-m-d H:i:s');

            $insert_phrases = [];
            foreach ($pack->phrases as $id => $phrase) {
                $groupname = \Orb\Util\Strings::rexplode('.', $id);
                $groupname = array_shift($groupname);

                $insert_phrases[] = [
                    'language_id'     => $lang_id,
                    'name'            => $id,
                    'groupname'       => $groupname,
                    'original_phrase' => $phrase,
                    'original_hash'   => sha1($phrase),
                    'created_at'      => $created_at,
                    'updated_at'      => $created_at,
                ];
            }

            $batch = array_chunk($insert_phrases, 150);
            foreach ($batch as $b) {
                $this->em->getConnection()->batchInsert('phrases', $b);
            }

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
            throw $e;
        }

        return $lang;
    }
}
