<?php

namespace DeskPRO\Bundle\AppBundle\AntiAbuse;

/**
 * Class AntiAbuseConfigSet.
 */
class AntiAbuseConfigSet
{
    /**
     * @var AntiAbuseConfig[]
     */
    private $configCollection;

    /**
     * @param AntiAbuseConfig $config
     *
     * @return $this
     */
    public function addConfig(AntiAbuseConfig $config)
    {
        $this->configCollection[] = $config;

        return $this;
    }

    /**
     * @return AntiAbuseConfig[]
     */
    public function getConfigCollection()
    {
        return $this->configCollection;
    }
}
