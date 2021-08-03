<?php

namespace Orb\Service\Microsoft\Translate;

/**
 * Class TwigTranslate
 * @package Orb\Service\Microsoft\Translate
 */
class TwigTranslate
{
    /**
     * @var Translate
     */
    private $translate;

    /**
     * Constructor.
     *
     * @param Translate $translate
     */
    public function __construct(Translate $translate)
    {
        $this->translate = $translate;
    }

    /**
     * @return array
     */
    public function getLanguagesForTranslate()
    {
        return $this->translate->getLanguagesForTranslate();
    }

    /**
     * @param string $langCode
     *
     * @return array
     */
    public function getSingleLanguageName($langCode)
    {
        return $this->translate->getSingleLanguageName($langCode);
    }
}
