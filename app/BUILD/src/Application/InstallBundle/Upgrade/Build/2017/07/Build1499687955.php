<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1499687955 extends AbstractBuild implements BlockingBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        if ($this->readSetting('rate_limit.registration.response') === 'captcha') {
            $this->saveSetting('rate_limit.registration.enabled', false);
        }
        if ($this->readSetting('rate_limit.reset_password.response') === 'captcha') {
            $this->saveSetting('rate_limit.reset_password.enabled', false);
        }

        $this->execDbQuery('default', 'DELETE FROM `settings` WHERE `name` = "rate_limit.registration.response"');
        $this->execDbQuery('default', 'DELETE FROM `settings` WHERE `name` = "rate_limit.reset_password.response"');
    }
}
