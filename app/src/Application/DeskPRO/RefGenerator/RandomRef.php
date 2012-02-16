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

namespace Application\DeskPRO\RefGenerator;

use Application\DeskPRO\App;

use Orb\Util\Strings;

class RandomRef implements RefGeneratorInterface
{
	/**
	 * @var \Application\DeskPRO\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @var \Application\DeskPRO\DBAL
	 */
	protected $db;

	public function __construct(\Doctrine\ORM\EntityManager $em)
	{
		$this->em = $em;
		$this->db = $em->getConnection();
	}

	public function generateReference($entity_name)
	{
		$table = $this->em->getClassMetadata(App::getEntityClass($entity_name))->getTableName();
		$field = 'ref';

		$stmt = $this->db->prepare("SELECT COUNT(*) FROM `$table` WHERE `$field` = ? LIMIT 1");

		do {
			$count = 0;

			$ref = Strings::random(8, Strings::CHARS_KEY);

			$stmt->execute(array($ref));
			$count = $stmt->fetchColumn();

		} while ($count > 0);

		return $ref;
	}

	/**
	 * Check if a string is a valid ref format. This only checks
	 * the format, no checking if it exists or anything like that.
	 *
	 * @param string $ref
	 * @return bool
	 */
	public function isRefMatch($ref)
	{
		return preg_match('#^([0-9A-Z]{8})$#', $ref);
	}

	/**
	 * Try to find all refs in a body of text and return an array of
	 * found matches.
	 *
	 * The order doesnt matter. But usually implementations will check refs
	 * in the order they appear in the array. So if there is such thing as priority,
	 * the first one should be the most likely match.
	 *
	 * @param $string
	 * @return string[]
	 */
	public function extractRefs($string, $ldelim = '\b', $rdelim = '\b')
	{
		return Strings::extractRegexMatch('#('.$ldelim.')([0-9A-Z]{8})('.$rdelim.')#', $string, 2);
	}
}