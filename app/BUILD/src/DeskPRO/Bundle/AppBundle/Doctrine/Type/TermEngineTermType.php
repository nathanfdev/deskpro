<?php

namespace DeskPRO\Bundle\AppBundle\Doctrine\Type;

use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\Util\TermToJsonConverter;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type as BaseType;

/**
 * A serializer that stores terms as JSON strings in the DB.
 */
class TermEngineTermType extends BaseType
{
    const TERM_ENGINE_TERM_TYPE = 'term_engine_term';

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
        if (!$value instanceof TermInterface) {
            throw new \InvalidArgumentException(
                'failed converting term engine value to json: must be an instance of TermInterface'
            );
        }

        $converter = new TermToJsonConverter();

        return $converter->toJson($value);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $converter = new TermToJsonConverter();

        return $converter->toTerm($value);
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::TERM_ENGINE_TERM_TYPE;
    }
}
