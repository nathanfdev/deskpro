<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1479817817 extends AbstractBuild
{
    public function run()
    {
        $this->out('Update system alerts tables');

        $sh = $this->getSchemaHelper();
        if ($sh->tableHasColumn('system_alerts_incidents', 'event_dates')) {
            $this->execDbQuery('system', 'ALTER TABLE system_alerts_incidents DROP event_dates');
        }
        $this->execDbQuery('system', 'DELETE FROM `system_alerts_incidents` WHERE `type` LIKE \'%email%\'');
        $this->execDbQuery('system', 'DELETE FROM `system_alerts_events` WHERE `type` LIKE \'%email%\'');
    }
}
