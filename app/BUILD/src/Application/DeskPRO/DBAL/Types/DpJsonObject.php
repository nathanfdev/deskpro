<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Orb\Types\JsonObjectSerializable;
use Orb\Types\JsonObjectSerializer;

class DpJsonObject extends Type
{
    const DP_JSON_OBJ = 'dp_json_obj';

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getClobTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return;
        }

        if (!is_object($value) || !($value instanceof JsonObjectSerializable)) {
            throw new \InvalidArgumentException('Class is not JsonObjectSerializable');
        }

        return JsonObjectSerializer::serialize($value);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return;
        }

        $value = (is_resource($value)) ? stream_get_contents($value) : $value;

        try {
            return JsonObjectSerializer::unserialize($value);
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::DP_JSON_OBJ;
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
