<?php

namespace DeskPRO\Bundle\UpdateBundle\BuildActivate\HelpdeskState;

use DeskPRO\Bundle\UpdateBundle\Instance\BuildInstance;

interface HelpdeskStateModifierInterface
{
    public function disableHelpdeskForUpdate();

    public function enableHelpdeskFromUpdate();

    /**
     * @param BuildInstance $build
     */
    public function setHelpdeskBuild(BuildInstance $build);
}
