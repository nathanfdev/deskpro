<?php

/**
 * DeskPRO.
 */

namespace Application\InstallBundle\Upgrade\Build;

use DeskPRO\Component\Util\ListUtils;

class BuildNewAgent_0005_triggers extends AbstractBuild
{
    public function run()
    {
        $this->out('Remove old triggers');
        $this->execDbQuery('default', "
            DELETE FROM ticket_triggers
            WHERE sys_name IN ('default_newticket_requirevalid', 'default_newticket_validemail', 'default_newticket_validagent')
        ");

        $this->out('Remove old trigger terms and actions');
        $db       = $this->container->get('doctrine.dbal.default_connection');
        $triggers = $db->fetchAll("
            SELECT id, terms, actions
            FROM ticket_triggers
            WHERE terms LIKE '%CheckUserValidAgent%' OR actions LIKE '%SetRequireValidation%'
        ");
        foreach ($triggers as $t) {
            $this->processTrigger($t);
        }
    }

    private function processTrigger(array $trigger)
    {
        static $badTerms   = ['CheckUserValidAgent'];
        static $badActions = ['SetRequireValidation'];

        $newTerms   = @json_decode($trigger['terms'], true) ?: null;
        $newActions = @json_decode($trigger['actions'], true) ?: null;

        // Make sure the payloads are in the shape we expect
        if (!$newTerms || empty($newTerms['@DATA']['terms'])) {
            $newTerms = null;
        }
        if (!$newActions || empty($newActions['@DATA']['actions'])) {
            $newActions = null;
        }

        // Filter out the old terms/actions
        if ($newTerms) {
            $newTerms['@DATA']['terms'] = ListUtils::filter($newTerms['@DATA']['terms'], function ($term) use ($badTerms) {
                return !in_array($term['type'], $badTerms);
            });
        }
        if ($newTerms) {
            $badActions['@DATA']['actions'] = ListUtils::filter($newTerms['@DATA']['actions'], function ($term) use ($badActions) {
                return !in_array($term['type'], $badActions);
            });
        }

        // If our filter made the resulting arrays empty, we can just delete the trigger entirely
        $doClean = false;
        if (!$newTerms || empty($newTerms['@DATA']['terms'])) {
            $doClean = true;
        }
        if (!$newActions || empty($newActions['@DATA']['actions'])) {
            $doClean = true;
        }

        if ($doClean) {
            $this->out("    - Removing trigger {$trigger['id']}");
            $db = $this->container->get('doctrine.dbal.default_connection');
            $db->delete('ticket_triggers', ['id' => $trigger['id']]);
        }
    }
}

//[[build:1460678405]]
