<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1465389242 extends AbstractBuild
{
    public function run()
    {
        $this->out('Add mobile trigger mode');

        $this->container->getDb()->executeUpdate("
            UPDATE ticket_triggers
            SET by_agent_mode = 'api,email,mobile,web'
            WHERE
              department_id IS NOT NULL
              AND event_trigger = 'newticket'
        ");

        $this->container->getDb()->executeUpdate("
            UPDATE ticket_triggers
            SET by_agent_mode = 'api,email,mobile,web'
            WHERE by_agent_mode = 'api,email,web'
        ");
    }
}
