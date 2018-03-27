<?php

namespace DeskPRO\Bundle\UpdateBundle\BuildActivate\UpgradeRunner;

use DeskPRO\Bundle\UpdateBundle\Instance\BuildInstance;

interface UpgradeRunnerInterface
{
    public function runUpgrade(BuildInstance $build);
}
