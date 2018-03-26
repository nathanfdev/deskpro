<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\People\AgentNotifPrefs;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Translate\Translate;

class PrefsTable
{
    /**
     * @var \Application\DeskPRO\Translate\Translate
     */
    private $tr;

    /**
     * @var Prefs
     */
    private $prefs;

    /**
     * @param Prefs     $prefs
     * @param Translate $tr
     */
    public function __construct(Prefs $prefs, Translate $tr)
    {
        $this->prefs = $prefs;
        $this->tr    = $tr;
    }

    /**
     * @return array
     */
    public function buildChatTable()
    {
        return $this->buildAppTable('chat');
    }

    /**
     * @return array
     */
    public function buildTaskTable()
    {
        return $this->buildAppTable('task');
    }

    /**
     * @return array
     */
    public function buildTwitterTable()
    {
        return $this->buildAppTable('twitter');
    }

    /**
     * @return array
     */
    public function buildFeedbackTable()
    {
        return $this->buildAppTable('feedback');
    }

    /**
     * @return array
     */
    public function buildPublishTable()
    {
        return $this->buildAppTable('publish');
    }

    /**
     * @return array
     */
    public function buildCrmTable()
    {
        return $this->buildAppTable('crm');
    }

    /**
     * @return array
     */
    public function buildAccountTable()
    {
        return $this->buildAppTable('account');
    }

    /**
     * @param string $app_name
     *
     * @return array
     */
    private function buildAppTable($app_name)
    {
        $cols = [];

        $method_name = 'get'.ucfirst($app_name).'NotifyTypes';

        $email_types = $this->prefs->$method_name('email');
        $alert_types = $this->prefs->$method_name('alert');

        if ($email_types) {
            $cols[] = ['name' => 'email', 'title' => $this->tr->phrase('agent.prefs.apps_email_title')];
        }
        if ($alert_types) {
            $cols[] = ['name' => 'alert', 'title' => $this->tr->phrase('agent.prefs.apps_alert_title')];
        }

        $values = [];

        $combined_types = [];
        foreach ($email_types as $type_name) {
            $base_type_name                  = preg_replace('#_email$#', '', $type_name);
            $combined_types[$base_type_name] = ['email'];

            foreach ($this->prefs->getAppSubs('email', $app_name) as $k => $v) {
                if ($v) {
                    $values["{$k}_email"] = true;
                } else {
                    $values["{$k}_email"] = false;
                }
            }
        }
        foreach ($alert_types as $type_name) {
            $base_type_name = preg_replace('#_alert#', '', $type_name);

            if (!isset($combined_types[$base_type_name])) {
                $combined_types[$base_type_name] = [];
            }
            $combined_types[$base_type_name][] = 'alert';

            foreach ($this->prefs->getAppSubs('alert', $app_name) as $k => $v) {
                if ($v) {
                    $values["{$k}_alert"] = true;
                } else {
                    $values["{$k}_alert"] = false;
                }
            }
        }

        $rows = [];
        foreach ($combined_types as $base_name => $types) {
            $row = [
                'name'  => $base_name,
                'title' => $this->tr->phrase("agent.prefs.apps_{$base_name}"),
                'cols'  => [],
            ];

            foreach ($types as $t) {
                $name          = "{$base_name}_$t";
                $row['cols'][] = [
                    'name'  => $name,
                    'value' => (isset($values[$name]) && $values[$name]) ? true : false,
                ];
            }

            $rows[] = $row;
        }

        return [
            'type'    => $app_name,
            'columns' => $cols,
            'rows'    => $rows,
        ];
    }

    /**
     * @param        $type
     * @param array  $sys_filters
     * @param Person $person_context
     *
     * @return array
     */
    public function buildSystemFiltersTable($type, array $sys_filters, Person $person_context = null)
    {
        // Sort filters
        usort($sys_filters, function ($a, $b) {
            $sys_order = ['agent', 'agent_team', 'participant', 'unassigned', 'all'];

            $a_order = array_search($a->sys_name, $sys_order, true) ?: 0;
            $b_order = array_search($b->sys_name, $sys_order, true) ?: 0;

            return $a_order < $b_order ? -1 : 1;
        });

        $sys_table_cols = [];
        foreach (['created', 'newleave', 'user_activity', 'agent_activity', 'agent_note', 'property_change'] as $c) {
            $sys_table_cols[] = [
                'name'  => $c,
                'title' => $this->tr->phrase("agent.prefs.inbox_{$c}_title"),
            ];
        }

        $sys_table_rows = [];
        foreach ($sys_filters as $f) {
            if (0 === strpos($f->sys_name, 'problem_')) {
                continue;
            }
            if ($f->sys_name == 'all') {
                $pref_opts = [
                    ['created'],
                    [],
                    ['user_activity'],
                    ['agent_activity'],
                    ['agent_note'],
                    ['property_change'],
                ];
            } else {
                $pref_opts = [
                    ['created'],
                    ['new', 'leave'],
                    ['user_activity'],
                    ['agent_activity'],
                    ['agent_note'],
                    ['property_change'],
                ];
            }

            $row = [
                'filter' => ['id' => $f->id, 'sys_name' => $f->sys_name, 'title' => $f->title],
                'cols'   => [],
            ];

            $values = $this->prefs->getFilterSubsForFilter($type, $f);

            foreach ($pref_opts as $opts) {
                $col = [];
                foreach ($opts as $opt) {
                    if (!$opt) {
                        $col[] = null;
                    } else {
                        if (!$person_context) {
                            $desc = $this->tr->phrase("agent.prefs.inbox_{$f->sys_name}_{$type}_{$opt}");
                        } else {
                            $desc = $this->tr->phrase("agent.prefs.name_inbox_{$f->sys_name}_{$type}_{$opt}", ['name' => $person_context->getDisplayName()]);
                        }
                        $col[] = [
                            'name'  => $opt,
                            'desc'  => $desc,
                            'value' => (isset($values[$opt]) && $values[$opt]) ? true : false,
                        ];
                    }
                }
                $row['cols'][] = $col;
            }

            $sys_table_rows[] = $row;
        }

        return [
            'columns' => $sys_table_cols,
            'rows'    => $sys_table_rows,
        ];
    }

    /**
     * @param string                                           $type
     * @param \Application\DeskPRO\Entity\LegacyTicketFilter[] $custom_filters
     *
     * @return array
     */
    public function buildCustomFiltersTable($type, array $custom_filters)
    {
        $custom_table_cols = [];
        foreach (['created', 'new', 'user_activity', 'agent_activity', 'agent_note', 'property_change'] as $c) {
            $custom_table_cols[] = [
                'name'  => $c,
                'title' => $this->tr->phrase("agent.prefs.filter_{$c}_title"),
            ];
        }

        $custom_table_rows = [];
        foreach ($custom_filters as $f) {
            $pref_opts = [
                ['created'],
                ['new'],
                ['user_activity'],
                ['agent_activity'],
                ['agent_note'],
                ['property_change'],
            ];

            $row = [
                'filter' => ['id' => $f->id, 'sys_name' => null, 'title' => $f->title],
                'cols'   => [],
            ];

            $values = $this->prefs->getFilterSubsForFilter($type, $f);

            foreach ($pref_opts as $opts) {
                $col = [];
                foreach ($opts as $opt) {
                    $col[] = [
                        'name'  => $opt,
                        'desc'  => $this->tr->phrase("agent.prefs.filter_{$type}_{$opt}_desc"),
                        'value' => (isset($values[$opt]) && $values[$opt]) ? true : false,
                    ];
                }
                $row['cols'][] = $col;
            }

            $custom_table_rows[] = $row;
        }

        return [
            'columns' => $custom_table_cols,
            'rows'    => $custom_table_rows,
        ];
    }
}
