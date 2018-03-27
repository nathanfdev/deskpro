<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1467639200 extends AbstractBuild
{
    public function run()
    {
        $this->out('Set feedback to reviewed if visible');
        $this->execDbQuery('default', "UPDATE feedback SET is_reviewed = 1 WHERE status != 'hidden'");
    }
}
