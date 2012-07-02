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
 */

namespace Application\DeskPRO\Languages;

use Doctrine\ORM\EntityManager;
use Application\DeskPRO\Entity\Language;

class LanguageInstaller
{
	protected $em;

	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}


	/**
	 * Install a language pack from a pack file located at $pack_path.
	 *
	 * @param string $pack_path
	 */
	public function insatllFromPackFilePath($pack_path)
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
	 * Install a new language pack
	 *
	 * @param \Application\DeskPRO\Languages\LanguagePack $pack
	 * @return \Application\DeskPRO\Entity\Language
	 * @throws \Exception
	 */
	public function installPack(LanguagePack $pack)
	{
		$lang = new Language();
		$lang->title  = $pack->title;
		$lang->locale = $pack->locale;

		$this->em->getConnection()->beginTransaction();
		try {

			$this->em->persist($lang);
			$this->em->flush();

			$lang_id = $lang->getId();
			$created_at = date('Y-m-d H:i:s');

			$insert_phrases = array();
			foreach ($pack->phrases as $id => $phrase) {

				$groupname = \Orb\Util\Strings::rexplode('.', $id);
				$groupname = array_shift($groupname);

				$insert_phrases[] = array(
					'language_id'       => $lang_id,
					'name'              => $id,
					'groupname'         => $groupname,
					'original_phrase'   => $phrase,
					'original_hash'     => sha1($phrase),
					'created_at'        => $created_at,
					'updated_at'        => $created_at,
				);
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