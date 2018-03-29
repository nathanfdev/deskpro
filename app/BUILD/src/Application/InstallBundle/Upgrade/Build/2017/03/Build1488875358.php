<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1488875358 extends AbstractBuild
{
    public function run()
    {
        $this->out('Replace UK country code with GB');
        $this->execDbQuery('default', 'UPDATE voice_numbers SET country_code="GB" WHERE country_code="UK"');
    }
}
