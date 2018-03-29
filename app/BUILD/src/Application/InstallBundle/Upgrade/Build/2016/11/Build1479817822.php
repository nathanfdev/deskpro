<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1479817822 extends AbstractBuild
{
    public function run()
    {
        $this->out('Add top bar onboarding');
        $this->execDbQuery('default', 'INSERT INTO person_onboarding 
      (`id`, `person_id`, `current_step`, `onboarding_class`, `status`, `application`)
        SELECT NULL, p.id, 0, \'topbarChanges\', 0, \'Agent\' FROM people p WHERE p.is_agent = 1');
    }
}
