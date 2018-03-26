<?php

namespace Application\DeskPRO\Debug\Data;

use Application\DeskPRO\App;

/**
 * Class TicketContextData.
 */
class TicketContextData implements DataInterface
{
    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $db = App::getDb();

        $deps             = $db->fetchAll('SELECT * FROM departments ORDER BY id ASC');
        $teams            = $db->fetchAll('SELECT * FROM agent_teams ORDER BY id ASC');
        $groups           = $db->fetchAll('SELECT * FROM usergroups ORDER BY id ASC');
        $products         = $db->fetchAll('SELECT * FROM products ORDER BY id ASC');
        $priorities       = $db->fetchAll('SELECT * FROM ticket_priorities ORDER BY id ASC');
        $workflows        = $db->fetchAll('SELECT * FROM ticket_workflows ORDER BY id ASC');
        $categories       = $db->fetchAll('SELECT * FROM ticket_categories ORDER BY id ASC');
        $ticketFields     = $db->fetchAll('SELECT * FROM custom_def_ticket ORDER BY id ASC');
        $personFields     = $db->fetchAll('SELECT * FROM custom_def_people ORDER BY id ASC');
        $orgFields        = $db->fetchAll('SELECT * FROM custom_def_organizations ORDER BY id ASC');
        $contextualFields = $db->fetchAll('SELECT * FROM custom_field_definition ORDER BY id ASC');
        $emailAccounts    = $db->fetchAll('SELECT id, account_type, is_enabled, address, other_addresses, date_created FROM email_accounts ORDER BY id ASC');
        $agents           = [];
        foreach (App::$container->getAgentData()->getAgents() as $a) {
            $agents[] = $a->toBasicApiData();
        }

        return [
            'departments'       => $deps,
            'agents'            => $agents,
            'agent_teams'       => $teams,
            'usergroups'        => $groups,
            'email_accounts'    => $emailAccounts,
            'products'          => $products,
            'priorities'        => $priorities,
            'workflows'         => $workflows,
            'categories'        => $categories,
            'ticket_fields'     => $ticketFields,
            'person_fields'     => $personFields,
            'org_fields'        => $orgFields,
            'contextual_fields' => $contextualFields,
        ];
    }
}
