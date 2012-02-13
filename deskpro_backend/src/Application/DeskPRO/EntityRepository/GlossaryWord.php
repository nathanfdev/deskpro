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

use Doctrine\ORM\EntityRepository;

class GlossaryWord extends EntityRepository
{
	/**
	 * Get a list of all words
	 */
	public function getWords()
	{
		$words = App::getDb()->fetchAllKeyValue("
			SELECT id, word
			FROM glossary_words
			ORDER BY word ASC
		");

		return $words;
	}
}