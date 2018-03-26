<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1465468468 extends AbstractBuild
{
    public function run()
    {
        $this->out('Update rate_limit_log ip');
        $this->execDbQuery('default', 'TRUNCATE TABLE `rate_limit_log`');
        $this->execDbQuery('default', 'ALTER TABLE `rate_limit_log` CHANGE `ip` `ip` VARCHAR(255) NOT NULL');
    }
}
