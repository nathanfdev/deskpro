<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1465578177 extends AbstractBuild
{
    public function run()
    {
        $this->out('CC in email source');
        $this->execDbQuery('default', 'ALTER TABLE email_sources ADD header_cc LONGTEXT NOT NULL AFTER header_to');
    }
}
