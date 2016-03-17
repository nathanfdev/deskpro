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

/**
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
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

use JMS\Serializer\Annotation as JMS;

/**
 * Class WidgetSetup.
 */
class WidgetSetup
{
    /**
     * A bunch of configuration described in urls.
     *
     * @JMS\Type("array<string, string>")
     *
     * @var array
     */
    protected $url;

    /**
     * Company settings.
     *
     * @JMS\Type("array<string, array<string, string>>")
     *
     * @var array
     */
    protected $company;

    /**
     * Widget settings itself.
     *
     * @JMS\Type("array<string, array<string, string>>")
     *
     * @var array
     */
    protected $settings;

    /**
     * If widget enabled on portal.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $enabled_on_portal;

    /**
     * WidgetSetup constructor.
     *
     * @param array $company
     * @param bool  $enabled_on_portal
     */
    public function __construct($company, $enabled_on_portal)
    {
        $this->company           = $company;
        $this->enabled_on_portal = $enabled_on_portal;
    }

    /**
     * @param array $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @param mixed $settings
     *
     * @return $this
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;

        return $this;
    }
}
