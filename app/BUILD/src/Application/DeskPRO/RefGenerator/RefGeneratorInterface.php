<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\RefGenerator;

interface RefGeneratorInterface
{
    /**
     * Generates a new reference number for the supplied object type.
     * Reference numbers must be at MOST 25 characters, and must be unique.
     *
     * @param  $class
     *
     * @return string
     */
    public function generateReference($class);

    /**
     * Check if a string is a valid ref format. This only checks
     * the format, no checking if it exists or anything like that.
     *
     * @param string $ref
     *
     * @return bool
     */
    public function isRefMatch($ref);

    /**
     * Try to find all refs in a body of text and return an array of
     * found matches.
     *
     * The order doesnt matter. But usually implementations will check refs
     * in the order they appear in the array. So if there is such thing as priority,
     * the first one should be the most likely match.
     *
     * @param string $string
     * @param string $ldelim The left delimeter that wraps the ref
     * @param string $rdelim The right delimeter that wraps the ref
     *
     * @return string[]
     */
    public function extractRefs($string, $ldelim = '\b', $rdelim = '\b');
}
