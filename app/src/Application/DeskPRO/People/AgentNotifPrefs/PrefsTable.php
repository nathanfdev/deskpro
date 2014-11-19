<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
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
     * @param  string $app_name
     * @return array
     */
    private function buildAppTable($app_name)
    {
        $cols = array();

        $method_name = 'get' . ucfirst($app_name) . 'NotifyTypes';

        $email_types = $this->prefs->$method_name('email');
        $alert_types = $this->prefs->$method_name('alert');

        if ($email_types) {
            $cols[] = array('name' => 'email', 'title' => $this->tr->phrase('agent.prefs.apps_email_title'));
        }
        if ($alert_types) {
            $cols[] = array('name' => 'alert', 'title' => $this->tr->phrase('agent.prefs.apps_alert_title'));
        }

        $values = array();

        $combined_types = array();
        foreach ($email_types as $type_name) {
            $base_type_name = preg_replace('#_email$#', '', $type_name);
            $combined_types[$base_type_name] = array('email');

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
                $combined_types[$base_type_name] = array();
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

        $rows = array();
        foreach ($combined_types as $base_name => $types) {
            $row = array(
                'name'   => $base_name,
                'title'  => $this->tr->phrase("agent.prefs.apps_{$base_name}"),
                'cols'   => array(),
            );

            foreach ($types as $t) {
                $name = "{$base_name}_$t";
                $row['cols'][] = array(
                    'name'  => $name,
                    'value' => (isset($values[$name]) && $values[$name]) ? true : false
                );
            }

            $rows[] = $row;
        }

        return array(
            'type'    => $app_name,
            'columns' => $cols,
            'rows'    => $rows,
        );
    }


    /**
     * @param         $type
     * @param  array  $sys_filters
     * @param  Person $person_context
     * @return array
     */
    public function buildSystemFiltersTable($type, array $sys_filters, Person $person_context = null)
    {
        // Sort filters
        usort($sys_filters, function ($a, $b) {
            $sys_order = array('agent', 'agent_team', 'participant', 'unassigned', 'all');

            $a_order = array_search($a->sys_name, $sys_order, true) ?: 0;
            $b_order = array_search($b->sys_name, $sys_order, true) ?: 0;

            return $a_order < $b_order ? -1 : 1;
        });

        $sys_table_cols = array();
        foreach (array('created', 'newleave', 'user_activity', 'agent_activity', 'agent_note', 'property_change') as $c) {
            $sys_table_cols[] = array(
                'name'  => $c,
                'title' => $this->tr->phrase("agent.prefs.inbox_{$c}_title")
            );
        }

        $sys_table_rows = array();
        foreach ($sys_filters as $f) {
            if ($f->sys_name == 'all') {
                $pref_opts = array(
                    array('created'),
                    array(),
                    array('user_activity'),
                    array('agent_activity'),
                    array('agent_note'),
                    array('property_change'),
                );
            } else {
                $pref_opts = array(
                    array('created'),
                    array('new', 'leave'),
                    array('user_activity'),
                    array('agent_activity'),
                    array('agent_note'),
                    array('property_change'),
                );
            }

            $row = array(
                'filter' => array('id' => $f->id, 'sys_name' => $f->sys_name, 'title' => $f->title),
                'cols'   => array(),
            );

            $values = $this->prefs->getFilterSubsForFilter($type, $f);

            foreach ($pref_opts as $opts) {
                $col = array();
                foreach ($opts as $opt) {
                    if (!$opt) {
                        $col[] = null;
                    } else {
                        if (!$person_context) {
                            $desc = $this->tr->phrase("agent.prefs.inbox_{$f->sys_name}_{$type}_{$opt}");
                        } else {
                            $desc = $this->tr->phrase("agent.prefs.name_inbox_{$f->sys_name}_{$type}_{$opt}", array('name' => $person_context->getDisplayName()));
                        }
                        $col[] = array(
                            'name'  => $opt,
                            'desc'  => $desc,
                            'value' => (isset($values[$opt]) && $values[$opt]) ? true : false
                        );
                    }
                }
                $row['cols'][] = $col;
            }

            $sys_table_rows[] = $row;
        }

        return array(
            'columns' => $sys_table_cols,
            'rows'    => $sys_table_rows,
        );
    }


    /**
     * @param  string                                     $type
     * @param  \Application\DeskPRO\Entity\TicketFilter[] $custom_filters
     * @return array
     */
    public function buildCustomFiltersTable($type, array $custom_filters)
    {
        $custom_table_cols = array();
        foreach (array('created', 'new', 'user_activity', 'agent_activity', 'agent_note', 'property_change') as $c) {
            $custom_table_cols[] = array(
                'name'  => $c,
                'title' => $this->tr->phrase("agent.prefs.filter_{$c}_title")
            );
        }

        $custom_table_rows = array();
        foreach ($custom_filters as $f) {
            $pref_opts = array(
                array('created'),
                array('new'),
                array('user_activity'),
                array('agent_activity'),
                array('agent_note'),
                array('property_change')
            );

            $row = array(
                'filter' => array('id' => $f->id, 'sys_name' => null, 'title' => $f->title),
                'cols'   => array(),
            );

            $values = $this->prefs->getFilterSubsForFilter($type, $f);

            foreach ($pref_opts as $opts) {
                $col = array();
                foreach ($opts as $opt) {
                    $col[] = array(
                        'name'  => $opt,
                        'desc'  => $this->tr->phrase("agent.prefs.filter_{$type}_{$opt}_desc"),
                        'value' => (isset($values[$opt]) && $values[$opt]) ? true : false
                    );
                }
                $row['cols'][] = $col;
            }

            $custom_table_rows[] = $row;
        }

        return array(
            'columns' => $custom_table_cols,
            'rows'    => $custom_table_rows,
        );
    }
}
