<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1464777281 extends AbstractBuild
{
    public function run()
    {
        $this->execDbQuery('default', 'ALTER TABLE rate_limit_log ADD is_lockout TINYINT(1) NOT NULL');
    }
}
