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

use \Orb\Util\Numbers;

class Phrase extends EntityRepository
{
	public function getPhraseForLanguage($phrase_name, $language = null)
	{
		try {
			if ($language === null OR $language === 0) {
				$q = $this->getEntityManager()->createQuery("
					SELECT p
					FROM DeskPRO:Phrase p
					WHERE p.language IS NULL AND p.name = ?1
				")->setParameters(array(1=>$phrase_name));
			} else {
				$q = $this->getEntityManager()->createQuery("
					SELECT p
					FROM DeskPRO:Phrase p
					WHERE p.language = ?1 AND p.name = ?2
				")->setParameters(array(1=>$language, 2=>$phrase_name));
			}

			$r = $q->getSingleResult();
			return $r;
		} catch (\Exception $e) {
			return null;
		}
	}

	public function getCustomPhraseNamesInLanguage($language)
	{
		$names = App::getDb()->fetchColumn("
			SELECT name
			FROM phrases
			WHERE language_id = ?
		", array($language['id']));

		return $names;
	}
}