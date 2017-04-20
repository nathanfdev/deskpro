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

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\ButtonSettings;

use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class WidgetBrandButtonSettings.
 */
class WidgetBrandButtonSettings
{
    const SIZE_SMALL  = 'small';
    const SIZE_MEDIUM = 'medium';
    const SIZE_LARGE  = 'large';

    /**
     * @var ArrayCollection|WidgetBrandButtonTranslation[]
     *
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\ButtonSettings\WidgetBrandButtonTranslation>")
     *
     * @Assert\Count(min=1)
     * @Assert\Valid()
     * @AppAssert\UniqueCollection(property="language")
     */
    private $translations;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @Assert\NotBlank()
     */
    private $size = self::SIZE_MEDIUM;

    /**
     * @var WidgetBrandButtonColorsSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\ButtonSettings\WidgetBrandButtonColorsSettings")
     * @Assert\Valid()
     */
    private $colors;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->colors       = new WidgetBrandButtonColorsSettings();
        $this->translations = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param string $size
     *
     * @return $this
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return WidgetBrandButtonColorsSettings
     */
    public function getColors()
    {
        return $this->colors;
    }

    /**
     * @param WidgetBrandButtonColorsSettings $colors
     *
     * @return $this
     */
    public function setColors(WidgetBrandButtonColorsSettings $colors)
    {
        $this->colors = $colors;

        return $this;
    }

    /**
     * @param WidgetBrandButtonTranslation[]|ArrayCollection $translations
     *
     * @return $this
     */
    public function setTranslations($translations)
    {
        $this->translations = $translations;

        return $this;
    }

    /**
     * @return ArrayCollection|WidgetBrandButtonTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param int $languageId
     *
     * @return WidgetBrandButtonTranslation|null
     */
    public function getTranslation($languageId)
    {
        return $this->translations->filter(function (WidgetBrandButtonTranslation $translation) use ($languageId) {
            return $translation->getLanguage() === $languageId;
        })->first();
    }
}
