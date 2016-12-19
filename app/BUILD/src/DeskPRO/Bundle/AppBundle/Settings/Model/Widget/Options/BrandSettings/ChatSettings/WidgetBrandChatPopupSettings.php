<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
}
