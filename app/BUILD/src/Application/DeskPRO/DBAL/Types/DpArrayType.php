<?php

/**
 * DeskPRO.
 *
 * @category Types
 */

namespace Application\DeskPRO\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ArrayType;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

class DpArrayType extends ArrayType
{
    public function getSQLDeclaration(array $fieldDeclaration, \Doctrine\DBAL\Platforms\AbstractPlatform $platform)
    {
        return 'LONGBLOB';
    }

    public function convertToPHPValue($value, \Doctrine\DBAL\Platforms\AbstractPlatform $platform)
    {
        try {
            if ($value === null) {
                return;
            }

            $value = (is_resource($value)) ? stream_get_contents($value) : $value;
            $val   = @unserialize($value);
            if ($val === false && $value != 'b:0;') {
                throw ConversionException::conversionFailed($value, $this->getName());
            }

            return $val;
        } catch (ConversionException $e) {
            return [];
        }
    }

    public function getName()
    {
        return Type::TARRAY;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
