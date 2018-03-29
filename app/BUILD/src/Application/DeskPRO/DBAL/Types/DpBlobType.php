<?php

/**
 * DeskPRO.
 *
 * @category Types
 */

namespace Application\DeskPRO\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\BlobType;

/**
 * Some enhancements to Doctrine's connection class.
 */
class DpBlobType extends BlobType
{
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        switch ($fieldDeclaration['length']) {
            case -1: return 'BINARY';
            case -2: return 'TINYBLOB';
            case -3: return 'BLOB';
            case -4: return 'MEDIUMBLOB';
            case -5: return 'LONGBLOB';
        }

        $type = $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);

        $type = str_replace(
            ['VARCHAR(', 'CHAR(', 'TINYTEXT', 'TEXT', 'MEDIUMTEXT', 'LONGTEXT'],
            ['VARBINARY(', 'BINARY(', 'TINYBLOB', 'BLOB', 'MEDIUMBLOB', 'LONGBLOB'],
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
        return 'dpblob';
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
