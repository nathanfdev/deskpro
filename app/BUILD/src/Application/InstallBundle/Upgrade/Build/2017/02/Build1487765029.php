<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1487765029 extends AbstractBuild
{
    public function run()
    {
        $this->out('Fix phone_numbers.guessed_type');
        $this->execDbQuery('default', 'ALTER TABLE phone_numbers CHANGE guessed_type guessed_type VARCHAR(255) NOT NULL');
    }
}
