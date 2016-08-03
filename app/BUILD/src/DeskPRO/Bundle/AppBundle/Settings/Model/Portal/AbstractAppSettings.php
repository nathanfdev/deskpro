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

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Portal;

use DeskPRO\Bundle\AppBundle\Settings\Model\AbstractBrandAwareSettings;
use JMS\Serializer\Annotation as JMS;

/**
 * Class AbstractAppSettings.
 */
abstract class AbstractAppSettings extends AbstractBrandAwareSettings
{
    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $enabled;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $tab_enabled;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $subscriptions;

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     *
     * @return AbstractAppSettings
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTabEnabled()
    {
        return $this->tab_enabled;
    }

    /**
     * @param bool $tab_enabled
     *
     * @return AbstractAppSettings
     */
    public function setTabEnabled($tab_enabled)
    {
        $this->tab_enabled = $tab_enabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSubscriptions()
    {
        return $this->subscriptions;
    }

    /**
     * @param bool $subscriptions
     *
     * @return AbstractAppSettings
     */
    public function setSubscriptions($subscriptions)
    {
        $this->subscriptions = $subscriptions;

        return $this;
    }
}
