<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1489146277 extends AbstractBuild
{
    public function run()
    {
        $this->out('Improve index on ban_ips');
        $idx = $this->getSchemaHelper()->findIndex('ban_ips', 'is_range');
        if ($idx) {
            $this->execDbQuery('default', "DROP INDEX {$idx->getName()} ON ban_ips");
        }

        $idx = $this->getSchemaHelper()->findIndex('ban_ips', ['is_range', 'banned_ip']);
        if (!$idx) {
            $this->execDbQuery('default', 'CREATE INDEX is_range_idx ON ban_ips (is_range, banned_ip)');
        }
    }
}
