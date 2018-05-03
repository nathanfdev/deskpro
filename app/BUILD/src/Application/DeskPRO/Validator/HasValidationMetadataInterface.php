<?php

/**
 * DeskPRO.
 */

/**
 * Objects that implement this interface should have some validation logic.
 */

namespace Application\DeskPRO\Validator;

use Symfony\Component\Validator\Mapping\ClassMetadata as ValidatorClassMetadata;

interface HasValidationMetadataInterface
{
    public static function loadValidatorMetadata(ValidatorClassMetadata $metadata);
}
