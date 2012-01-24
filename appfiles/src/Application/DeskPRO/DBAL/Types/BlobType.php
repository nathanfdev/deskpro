<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Types
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\DBAL\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Some enhancements to Doctrine's connection class.
 */
class BlobType extends Type
{
	public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
	{
		$type = $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);

		$type = str_replace(
			array('VARCHAR(', 'CHAR(', 'TINYTEXT', 'TEXT', 'MEDIUMTEXT', 'LONGTEXT'),
			array('VARBINARY(', 'BINARY(', 'TINYBLOB', 'BLOB', 'MEDIUMBLOB', 'LONGBLOB'),
			$type
		);

		return $type;
	}

	public function convertToDatabaseValue($value, AbstractPlatform $platform)
	{
		return ($value === null) ? null : $value;
	}

	public function convertToPHPValue($value, AbstractPlatform $platform)
	{
		return ($value === null) ? null : $value;
	}

	public function getName()
	{
		return 'blob';
	}
}
