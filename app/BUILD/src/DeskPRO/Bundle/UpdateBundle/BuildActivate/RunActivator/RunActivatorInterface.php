<?php

namespace DeskPRO\Bundle\UpdateBundle\BuildActivate\RunActivator;

use DeskPRO\Bundle\UpdateBundle\Instance\BuildInstance;

interface RunActivatorInterface
{
    public function activateRunDir(BuildInstance $build);
}
