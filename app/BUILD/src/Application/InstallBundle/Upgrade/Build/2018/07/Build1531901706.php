<?php

namespace Application\InstallBundle\Upgrade\Build;

use Application\DeskPRO\Tickets\Actions\SetFlag;
use Orb\Types\JsonObjectSerializer;

class Build1531901706 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $triggers = $this->getDbConnection('default')->fetchAll("SELECT id, actions FROM `ticket_triggers` WHERE actions LIKE '%SetFlag%'");
        foreach ($triggers as $trigger) {
            try {
                $actions = JsonObjectSerializer::unserialize($trigger['actions']);
            } catch (\Exception $e) {
                $actions = null;
            }

            $hasChanged = false;
            if ($actions) {
                foreach ($actions as $action) {
                    if ($action instanceof SetFlag) {
                        if (!$action->getActionOptions()->has('agent_ids')) {
                            $action->getActionOptions()->set('agent_ids', ['all_agents']);
                            $hasChanged = true;
                        }
                    }
                }
            }

            if ($hasChanged) {
                $statement = $this->getDbConnection('default')->prepare('UPDATE `ticket_triggers` SET actions = :actions WHERE `id` = :id');
                $statement->execute([
                    'id'      => $trigger['id'],
                    'actions' => JsonObjectSerializer::serialize($actions),
                ]);
            }
        }
    }
}
