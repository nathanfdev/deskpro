<?php

namespace Application\DeskPRO\Form\Transformer;

use Orb\Util\PhoneNumbers;
use Symfony\Component\Form\DataTransformerInterface;

class PhoneNumberModelTransformer implements DataTransformerInterface
{
    public function transform($number)
    {
        return $number;
    }

    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param string $number
     *
     * @throws TransformationFailedException if object (issue) is not found
     *
     * @return Issue|null
     */
    public function reverseTransform($number)
    {
        if (!$number) {
            return;
        }

        try {
            if (!$formatted = PhoneNumbers::toE164Format($number)) {
                return;
            }
            if (!$region = PhoneNumbers::getRegionForNumber($number)) {
                return;
            }
        } catch (\Exception $e) {
            return;
        }

        return $formatted;
    }
}
