<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use Application\DeskPRO\Entity\Language as LanguageEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Language.
 */
class Language
{
    /**
     * The unique ID.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * The unique sys name assigned to the language.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $sysName;

    /**
     * The three-letter ISO 639-2 code.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $langCode;

    /**
     * Title of the language.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $title;

    /**
     * The locale code.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $locale;

    /**
     * True if this is a right-to-left language.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $isRtl;

    /**
     * True if has user.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $hasUser;

    /**
     * True if has agent.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $hasAgent;

    /**
     * True if has admin.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $hasAdmin;

    /**
     * String path to image.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $flagImage;

    /**
     * Constructor.
     *
     * @param LanguageEntity $language
     * @param string         $flagImage
     */
    public function __construct(LanguageEntity $language, $flagImage)
    {
        $this->id        = $language->getId();
        $this->sysName   = $language->getSystemName();
        $this->title     = $language->getTitle();
        $this->locale    = $language->getLocale();
        $this->langCode  = $language->getLangCode();
        $this->isRtl     = $language->isRtl();
        $this->hasUser   = $language->hasUser();
        $this->hasAgent  = $language->hasAgent();
        $this->hasAdmin  = $language->hasAdmin();
        $this->flagImage = $flagImage;
    }
}
