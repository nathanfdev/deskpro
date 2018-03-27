<?php

namespace DeskPRO\Bundle\AppBundle\AppEnv;

class FeatureFlagsFactory
{
    /**
     * @return \DpSys\Features
     */
    public static function create()
    {
        return \DpSys\Features::getInstance();
    }
}
