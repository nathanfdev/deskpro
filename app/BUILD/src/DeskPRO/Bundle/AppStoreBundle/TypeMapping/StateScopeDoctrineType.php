<?php
namespace DeskPRO\Bundle\AppStoreBundle\TypeMapping;

use DeskPRO\Bundle\AppStoreBundle\Domain\StateScope;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class StateScopeDoctrineType extends Type
{
    const TYPE = 'appstore_state_scope'; // modify to match your type name

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return StateScope::parseString($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof StateScope) {
            return StateScope::convertToString($value);
        }
    }

    public function getName()
    {
        return self::TYPE; // modify to match your constant name
    }
}
