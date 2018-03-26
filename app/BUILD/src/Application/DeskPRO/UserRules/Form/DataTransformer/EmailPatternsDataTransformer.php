<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\UserRules\Form\DataTransformer;

use Orb\Util\Arrays;
use Orb\Util\Strings;
use Symfony\Component\Form\DataTransformerInterface;

class EmailPatternsDataTransformer implements DataTransformerInterface
{
    /**
     * Transforms an array representation of email_patterns to a string representation.
     *
     * @param array $email_patterns_array
     *
     * @return string
     */
    public function transform($email_patterns_array)
    {
        return implode("\n", $email_patterns_array);
    }

    /**
     * Transforms email_patterns string (email_patterns from from) to an array representation
     * This array representation is used inside UserRule entity.
     *
     * @param string $email_patterns_string
     *
     * @return array
     */
    public function reverseTransform($email_patterns_string)
    {
        if (!$email_patterns_string) {
            return [];
        }

        $items = [];

        $patterns = Strings::standardEol($email_patterns_string);
        $patterns = explode("\n", $patterns);

        foreach ($patterns as $p) {
            $p       = Strings::utf8_strtolower($p);
            $items[] = trim($p);
        }

        $items = Arrays::removeFalsey($items);

        return $items;
    }
}
