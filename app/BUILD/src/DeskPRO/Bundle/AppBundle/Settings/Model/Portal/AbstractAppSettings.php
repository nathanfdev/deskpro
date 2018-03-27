<?php

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
