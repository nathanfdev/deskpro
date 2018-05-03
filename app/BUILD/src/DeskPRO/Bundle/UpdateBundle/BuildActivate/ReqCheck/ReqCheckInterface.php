<?php

namespace DeskPRO\Bundle\UpdateBundle\BuildActivate\ReqCheck;

use DeskPRO\Bundle\UpdateBundle\Instance\BuildInstance;

interface ReqCheckInterface
{
    /**
     * Checks if the provided build meets requirements, else it should throw an exception.
     *
     * @param BuildInstance $build
     */
    public function assertValidRequirements(BuildInstance $build);
}
