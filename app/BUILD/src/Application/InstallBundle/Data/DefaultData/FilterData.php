<?php

/**
 * DeskPRO.
 *
 * @category Install
 */

namespace Application\InstallBundle\Data\DefaultData;

class FilterData extends AbstractDefaultData
{
    public function runInstall()
    {
        $this->oldFilterInstall();
    }

    private function oldFilterInstall()
    {
        //------------------------------
        // Define filters
        //------------------------------

        $filters = [];

        $filters[] = [
            'title'    => 'My Tickets',
            'sys_name' => 'agent',
            'order_by' => 'ticket.urgency:desc',
            'terms'    => [
                ['type' => 'agent', 'op' => 'is', 'options' => ['agent' => '-1']],
                [
                    'type'    => 'status',
                    'op'      => 'is',
                    'options' => ['status' => 'awaiting_agent'],
                ],
            ],
        ];

        $filters[] = [
            'title'    => 'My Team\'s Tickets',
            'sys_name' => 'agent_team',
            'order_by' => 'ticket.urgency:desc',
            'terms'    => [
                ['type' => 'agent_team', 'op' => 'is', 'options' => ['agent_team' => '-1']],
                [
                    'type'    => 'status',
                    'op'      => 'is',
                    'options' => ['status' => 'awaiting_agent'],
                ],
            ],
        ];

        $filters[] = [
            'title'    => 'Tickets I Follow',
            'sys_name' => 'participant',
            'order_by' => 'ticket.urgency:desc',
            'terms'    => [
                ['type' => 'participant', 'op' => 'is', 'options' => ['agent' => '-1']],
                [
                    'type'    => 'status',
                    'op'      => 'is',
                    'options' => ['status' => 'awaiting_agent'],
                ],
            ],
        ];

        $filters[] = [
            'title'    => 'Unassigned',
            'sys_name' => 'unassigned',
            'order_by' => 'ticket.urgency:desc',
            'terms'    => [
                ['type' => 'agent', 'op' => 'is', 'options' => ['agent' => '0']],
                ['type' => 'agent_team', 'op' => 'is', 'options' => ['agent_team' => '0']],
                [
                    'type'    => 'status',
                    'op'      => 'is',
                    'options' => ['status' => 'awaiting_agent'],
                ],
            ],
        ];

        $filters[] = [
            'title'    => 'All',
            'sys_name' => 'all',
            'order_by' => 'ticket.urgency:desc',
            'terms'    => [
                [
                    'type'    => 'status',
                    'op'      => 'is',
                    'options' => ['status' => 'awaiting_agent'],
                ],
            ],
        ];

        $filters[] = [
            'title'    => 'Awaiting User',
            'sys_name' => 'archive_awaiting_user',
            'order_by' => 'ticket.urgency:desc',
            'terms'    => [
                [
                    'type'    => 'status',
                    'op'      => 'is',
                    'options' => ['status' => 'awaiting_user'],
                ],
            ],
        ];

        $filters[] = [
            'title'    => 'Resolved',
            'sys_name' => 'archive_resolved',
            'order_by' => 'ticket.urgency:desc',
            'terms'    => [
                [
                    'type'    => 'status',
                    'op'      => 'is',
                    'options' => ['status' => 'resolved'],
                ],
            ],
        ];

        $filters[] = [
            'title'    => 'Archived',
            'sys_name' => 'archive_archived',
            'order_by' => 'ticket.urgency:desc',
            'terms'    => [
                [
                    'type'    => 'status',
                    'op'      => 'is',
                    'options' => ['status' => 'archived'],
                ],
            ],
        ];

        $spamStatusId = (int) $this->getDb()->fetchColumn("SELECT id FROM ticket_statuses where sys_id = 'spam'");
        $filters[]    = [
            'title'    => 'Spam',
            'sys_name' => 'archive_spam',
            'order_by' => 'ticket.urgency:desc',
            'terms'    => [
                [
                    'type'    => 'status',
                    'op'      => 'is',
                    'options' => ['status' => 'hidden.'.$spamStatusId],
                ],
            ],
        ];

        $deletedStatusId = (int) $this->getDb()->fetchColumn("SELECT id FROM ticket_statuses where sys_id = 'deleted'");
        $filters[]       = [
            'title'    => 'Deleted',
            'sys_name' => 'archive_deleted',
            'order_by' => 'ticket.urgency:desc',
            'terms'    => [
                [
                    'type'    => 'status',
                    'op'      => 'is',
                    'options' => ['status' => 'hidden.'.$deletedStatusId],
                ],
            ],
        ];

        //------------------------------
        // Insert filters
        //------------------------------

        $exist_id_map = $this->getDb()->fetchAllKeyValue(
            '
            SELECT sys_name, id
            FROM ticket_filters
            WHERE sys_name IS NOT NULL
        '
        );

        $order = 1;
        foreach ([0, 1] as $is_hold) {
            foreach ($filters as $f) {
                $is_archive = strpos($f['sys_name'], 'archive_') === 0;

                if ($is_archive && $is_hold) {
                    continue;
                }

                $f['is_global']     = 1;
                $f['is_enabled']    = 1;
                $f['display_order'] = $order++;

                if ($is_hold) {
                    $f['title'] .= ' (Hold)';
                    $f['sys_name'] .= '_w_hold';
                    $f['terms'] = array_filter($f['terms'], function ($term) {
                        return $term['type'] != 'status';
                    });
                }

                if (!$is_archive) {
                    $f['terms'][] = ['type' => 'is_hold', 'op' => 'is', 'options' => ['is_hold' => $is_hold]];
                }

                $f['terms'] = json_encode($f['terms']);

                $exist_id = isset($exist_id_map[$f['sys_name']]) ? $exist_id_map[$f['sys_name']] : null;
                if ($exist_id) {
                    $this->getDb()->update('ticket_filters', $f, ['id' => $exist_id]);
                } else {
                    $this->getDb()->insert('ticket_filters', $f);
                }
            }
        }
    }

    public function runReset()
    {
        $this->getDb()->exec('DELETE FROM ticket_filters2');
        $this->getDb()->exec('DELETE FROM ticket_filters2_sets');
        $this->oldFilterInstall();
    }

    public function runSync()
    {
        $this->oldFilterInstall();
    }
}
