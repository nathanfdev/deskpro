<?php

namespace Application\InstallBundle\Upgrade\Build;

/**
 * Do not implement SkipPostBuildInterface here to reload Default FilterData.
 */
class Build1543397095 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $db = $this->getDbConnection();

        $spamId    = (int) $db->fetchColumn("SELECT id FROM ticket_statuses where sys_id = 'spam'");
        $deletedId = (int) $db->fetchColumn("SELECT id FROM ticket_statuses where sys_id = 'deleted'");

        // filters
        $this->replace('ticket_filters', 'terms', 'hidden.deleted', 'hidden.'.$deletedId);
        $this->replace('ticket_filters', 'terms', 'hidden.spam', 'hidden.'.$spamId);

        // escalations
        $this->replace('ticket_escalations', 'terms', 'hidden.deleted', 'hidden.'.$deletedId);
        $this->replace('ticket_escalations', 'terms', 'hidden.spam', 'hidden.'.$spamId);
        $this->replace('ticket_escalations', 'actions', 'hidden.deleted', 'hidden.'.$deletedId);
        $this->replace('ticket_escalations', 'actions', 'hidden.spam', 'hidden.'.$spamId);

        // trigger
        $this->replace('ticket_triggers', 'terms', 'hidden.deleted', 'hidden.'.$deletedId);
        $this->replace('ticket_triggers', 'terms', 'hidden.spam', 'hidden.'.$spamId);
        $this->replace('ticket_triggers', 'actions', 'hidden.deleted', 'hidden.'.$deletedId);
        $this->replace('ticket_triggers', 'actions', 'hidden.spam', 'hidden.'.$spamId);
    }

    protected function replace($table, $column, $statusFrom, $statusTo)
    {
        $this->execDbQuery('default', sprintf(
            'UPDATE %s SET %s = replace(%s, \'"%s"\', \'"%s"\') WHERE %s LIKE \'%%%s%%\'',
            $table, $column, $column, $statusFrom, $statusTo, $column, $statusFrom
        ));
    }
}
