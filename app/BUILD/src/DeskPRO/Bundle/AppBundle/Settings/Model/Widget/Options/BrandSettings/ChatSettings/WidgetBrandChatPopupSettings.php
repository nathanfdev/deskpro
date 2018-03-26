<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\ChatSettings;

use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class WidgetBrandChatPopupSettings.
 */
class WidgetBrandChatPopupSettings
{
    const STYLE_AGENT_TEXT_BUTTON   = 'agent_text_button';
    const STYLE_AGENT_TEXT_INPUT    = 'agent_text_input';
    const STYLE_AGENTS_BUTTON       = 'agents_button';
    const STYLE_TEXT_BUTTON         = 'text_button';
    const STYLE_TEXT_INPUT          = 'text_input';
    const STYLE_WIDGET_BUTTON_AGENT = 'widget_button_agent';

    /**
     * @var ArrayCollection|WidgetBrandChatPopupTranslation[]
     *
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\ChatSettings\WidgetBrandChatPopupTranslation>")
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
    private $style = self::STYLE_AGENT_TEXT_BUTTON;

    /**
     * @var float
     *
     * @JMS\Type("float")
     */
    private $delay = 0;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * @param WidgetBrandChatPopupTranslation[]|ArrayCollection $translations
     *
     * @return $this
     */
    public function setTranslations(ArrayCollection $translations)
    {
        $this->translations = $translations;

        return $this;
    }

    /**
     * @return ArrayCollection|WidgetBrandChatPopupTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param int $languageId
     *
     * @return WidgetBrandChatPopupTranslation|null
     */
    public function getTranslation($languageId)
    {
        return $this->translations->filter(function (WidgetBrandChatPopupTranslation $translation) use ($languageId) {
            return $translation->getLanguage() === $languageId;
        })->first();
    }

    /**
     * @return string
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * @param string $style
     *
     * @return $this
     */
    public function setStyle($style)
    {
        $this->style = $style;

        return $this;
    }

    /**
     * @return float
     */
    public function getDelay()
    {
        return $this->delay;
    }

    /**
     * @param float $delay
     *
     * @return $this
     */
    public function setDelay($delay)
    {
        $this->delay = $delay;

        return $this;
    }
}
