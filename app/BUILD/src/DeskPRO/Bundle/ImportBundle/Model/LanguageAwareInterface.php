<?php

namespace DeskPRO\Bundle\ImportBundle\Model;

/**
 * Basic properties on language interface.
 *
 * Interface LanguageAwareInterface
 */
interface LanguageAwareInterface
{
    /**
     * Returns an entity language.
     *
     * @return string
     */
    public function getLanguage();

    /**
     * Set an entity language.
     *
     * @param string $language
     *
     * @return $this
     */
    public function setLanguage($language);
}
