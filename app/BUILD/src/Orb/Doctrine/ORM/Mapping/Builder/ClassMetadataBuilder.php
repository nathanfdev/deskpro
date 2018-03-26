<?php

/**
 * Orb.
 */

namespace Orb\Doctrine\ORM\Mapping\Builder;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder as BaseBuilder;

class ClassMetadataBuilder extends BaseBuilder
{
    public function mapId($fieldName = 'id', $type = 'AUTO')
    {
        $this->createField($fieldName, 'integer')->generatedValue($type)->isPrimaryKey()->build();
    }

    public function mapString($fieldName, $length = 256, $nullable = true, $unique = false)
    {
        $this->addField(
            $fieldName, 'string', ['nullable' => $nullable, 'length' => $length, 'unique' => $unique]
        );
    }

    public function mapText($fieldName, $nullable = true)
    {
        $this->addField($fieldName, 'text', ['nullable' => $nullable]);
    }

    public function mapBoolean($fieldName)
    {
        $this->addField($fieldName, 'boolean');
    }

    public function mapInteger($fieldName, $nullable = true, $precision = 0, $scale = 0)
    {
        $this->addField(
            $fieldName,
            'integer',
            [
                'precision' => $precision,
                'scale'     => $scale,
                'nullable'  => $nullable,
            ]
        );
    }

    public function mapDateTime($fieldName, $nullable = true)
    {
        $this->addField(
            $fieldName, 'datetime', ['nullable' => $nullable]
        );
    }
}
