<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Translate;

use Application\DeskPRO\Entity\Language;

class DelegatePhrase implements DelegatePhraseInterface
{
    /** @var string */
    protected $phrase_name;
    /** @var array */
    protected $phrase_vars = [];

    public function __construct($phrase_name, array $phrase_vars = [])
    {
        $this->phrase_name = $phrase_name;
        $this->phrase_vars = $phrase_vars;
    }

    /**
     * Get the phrase text.
     *
     * @param  $translator
     *
     * @return string
     */
    public function getPhrase(Translate $translator, Language $language = null)
    {
        return $translator->phrase($this->phrase_name, $this->phrase_vars, $language);
    }

    /**
     * @return string
     */
    public function getPhraseName()
    {
        return $this->phrase_name;
    }

    /**
     * @return array
     */
    public function getPhraseVars()
    {
        return $this->phrase_vars;
    }

    public function __toString()
    {
        return $this->phrase_name;
    }
}
