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

use JMS\Serializer\Annotation as JMS;

/**
 * Class WidgetBrandButtonColorsSettings.
 */
class WidgetBrandButtonColorsSettings
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $background = '#62ad8c';

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $text = '#ffffff';

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $border = '#4e9576';

    /**
     * @return string
     */
    public function getBorder()
    {
        return $this->border;
    }

    /**
     * @param string $border
     */
    public function setBorder($border)
    {
        $this->border = $border;
    }

    /**
     * @return string
     */
    public function getBackground()
    {
        return $this->background;
    }

    /**
     * @param string $background
     */
    public function setBackground($background)
    {
        $this->background = $background;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }
}
