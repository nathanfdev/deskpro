<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
