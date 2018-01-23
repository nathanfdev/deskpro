<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 *
 * @category Install
 */

namespace Application\InstallBundle\Data\DefaultData;

use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet;

class FilterData extends AbstractDefaultData
{
    public function runInstall()
    {
        $this->oldFilterInstall();
        $this->newFilterInstall();
    }

    private function newFilterInstall()
    {
        $em = $this->getEm();

        $set = new TicketFilterSet();
        $set->setTitle('Inbox');
        $set->setDisplayOrder(0);
        $set->enableGlobalSharing();
        $em->persist($set);

        $f = new TicketFilter();
        $f->setTitle('Assigned To Me');
        $f->setDisplayOrder(10);
        $f->setQuery('ticket.status = \'Awaiting Agent\' AND ticket.agent = $me');
        $set->addFilter($f);
        $em->persist($f);

        $f = new TicketFilter();
        $f->setTitle('Tickets I Follow');
        $f->setDisplayOrder(10);
        $f->setQuery('ticket.status = \'Awaiting Agent\' AND ticket.followers HAS $me');
        $set->addFilter($f);
        $em->persist($f);

        $f = new TicketFilter();
        $f->setTitle('Assigned To Team');
        $f->setDisplayOrder(10);
        $f->setQuery('ticket.status = \'Awaiting Agent\' AND ticket.agent_team IN $my_teams');
        $set->addFilter($f);
        $em->persist($f);

        $f = new TicketFilter();
        $f->setTitle('Unassigned');
        $f->setDisplayOrder(10);
        $f->setQuery('ticket.status = \'Awaiting Agent\' AND ticket.agent IS NULL');
        $set->addFilter($f);
        $em->persist($f);

        $f = new TicketFilter();
        $f->setTitle('All Awaiting Agent');
        $f->setDisplayOrder(10);
        $f->setQuery('ticket.status = \'Awaiting Agent\'');
        $set->addFilter($f);
        $em->persist($f);

        $em->flush();
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

        $filters[] = [
            'title'    => 'Spam',
            'sys_name' => 'archive_spam',
            'order_by' => 'ticket.urgency:desc',
            'terms'    => [
                [
                    'type'    => 'status',
                    'op'      => 'is',
                    'options' => ['status' => 'hidden.spam'],
                ],
            ],
        ];

        $filters[] = [
            'title'    => 'Deleted',
            'sys_name' => 'archive_deleted',
            'order_by' => 'ticket.urgency:desc',
            'terms'    => [
                [
                    'type'    => 'status',
                    'op'      => 'is',
                    'options' => ['status' => 'hidden.deleted'],
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

        $this->newFilterInstall();
    }

    public function runSync()
    {
        $this->oldFilterInstall();
    }
}
