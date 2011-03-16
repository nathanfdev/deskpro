<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage RefGenerator
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace \Application\DeskPRO\RefGenerator;

use \Application\DeskPRO\App;

use \Orb\Util\Strings;

class RandomRef implements RefGeneratorInterface
{
	public function generateReference($entity_name)
	{
		$table = App::getEntityRepository($entity_name)->getClassMetadata()->getTableName();
		$field = 'ref';

		$stmt = App::getDb()->prepare("SELECT COUNT(*) FROM `$table` WHERE `$field` = ? LIMIT 1");

		do {
			$count = 0;

			$ref = Strings::random(3, Strings::CHARS_KEY_ALPHA);
			$ref .= '-' . Strings::random(3, Strings::CHARS_KEY_NUM);
			$ref .= '-' . Strings::random(3, Strings::CHARS_KEY_ALPHA);

			$stmt->execute(array($ref));
			$count = $stmt->fetchColumn();

		} while ($count > 0);

		return $ref;
	}
}