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

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Widget;

use JMS\Serializer\Annotation as JMS;

/**
 * Class WidgetUrlSettings.
 */
class WidgetUrlSettings
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $widgetLoader;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $widgetBundle;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $helpdesk;

    /**
     * @return string
     */
    public function getWidgetLoader()
    {
        return $this->widgetLoader;
    }

    /**
     * @param string $widgetLoader
     *
     * @return $this
     */
    public function setWidgetLoader($widgetLoader)
    {
        $this->widgetLoader = $widgetLoader;

        return $this;
    }

    /**
     * @return string
     */
    public function getWidgetBundle()
    {
        return $this->widgetBundle;
    }

    /**
     * @param string $widgetBundle
     *
     * @return $this
     */
    public function setWidgetBundle($widgetBundle)
    {
        $this->widgetBundle = $widgetBundle;

        return $this;
    }

    /**
     * @return string
     */
    public function getHelpdesk()
    {
        return $this->helpdesk;
    }

    /**
     * @param string $helpdesk
     *
     * @return $this
     */
    public function setHelpdesk($helpdesk)
    {
        $this->helpdesk = $helpdesk;

        return $this;
    }
}
