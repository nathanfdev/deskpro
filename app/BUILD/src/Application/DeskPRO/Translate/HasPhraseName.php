<?php

/**
 * DeskPRO.
 *
 * @category Translate
 */

namespace Application\DeskPRO\Translate;

/**
 * Objects that implement this interface will be compatible with Translate.
 */
interface HasPhraseName
{
    /**
     * Return a unique ID that we can use to look up translations for this object.
     *
     * @param string $property If supplied, the property on the object we want to translate
     *
     * @return string
     */
    public function getPhraseName($property);

    /**
     * Get the default value phrase for the object.
     *
     * @param string    $property  If supplied, the property on the object we want to translate
     * @param Translate $translate The translate object requesting
     *
     * @return string
     */
    public function getPhraseDefault($property, Translate $translate);
}
