<?php

/**
 * DeskPRO.
 *
 * @category Translate
 */

namespace Application\DeskPRO\Translate\Loader;

/**
 * A loader is a class that can load phrases from some resource.
 */
interface LoaderInterface
{
    /**
     * @param array $groups
     * @param mixed $language
     * @param $loaded_phrases
     *
     * @return array
     */
    public function load($groups, $language, array $loaded_phrases = null);
}
