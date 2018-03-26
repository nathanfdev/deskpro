<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\People\AgentNotifPrefs;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TicketFilterSubscription;
use Doctrine\ORM\EntityManager;
use Orb\Util\Arrays;

class PrefsPersister
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    private $person;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $db;

    public function __construct(Person $person, EntityManager $em)
    {
        $this->person = $person;
        $this->em     = $em;
        $this->db     = $em->getConnection();
    }

    /**
     * @param Prefs $prefs
     *
     * @throws \Exception
     */
    public function savePrefs(Prefs $prefs)
    {
        $filters = $this->em->getRepository('DeskPRO:LegacyTicketFilter')->getFiltersForPerson($this->person);
        $filters = Arrays::keyFromData($filters, 'id');

        //------------------------------
        // Create sub records
        //------------------------------

        $filter_subs = [];

        $person     = $this->person;
        $fn_get_sub = function ($filter_id) use (&$filter_subs, $person, $filters) {
            if (!isset($filter_subs[$filter_id])) {
                $filter_subs[$filter_id]         = new TicketFilterSubscription();
                $filter_subs[$filter_id]->filter = $filters[$filter_id];
                $filter_subs[$filter_id]->person = $person;
            }

            return $filter_subs[$filter_id];
        };

        foreach ($prefs->getFilterSubs('email') as $filter_id => $subs) {
            $sub = $fn_get_sub($filter_id);

            foreach ($subs as $name => $v) {
                if (!$v) {
                    continue;
                }
                $sub->{'email_'.$name} = true;
            }
        }
        foreach ($prefs->getFilterSubs('alert') as $filter_id => $subs) {
            $sub = $fn_get_sub($filter_id);

            foreach ($subs as $name => $v) {
                if (!$v) {
                    continue;
                }
                $sub->{'alert_'.$name} = true;
            }
        }

        //------------------------------
        // Pref records
        //------------------------------

        $pref_records = [];

        foreach ([
             'chat',
             'task',
             'twitter',
             'feedback',
             'publish',
             'crm',
             'account',
        ] as $app_name) {
            foreach (['email', 'alert'] as $type) {
                $subs = $prefs->getAppSubs($type, $app_name);
                foreach ($subs as $name => $v) {
                    if (!$v) {
                        continue;
                    }
                    $pref_records[] = [
                        'person_id' => $this->person->id,
                        'name'      => "agent_notif.{$name}.$type",
                        'value_str' => '1',
                    ];
                }
            }
        }

        // todo
        if ($this->person->getPref('agent_notif.no_allow_set_email')) {
            $pref_records[] = [
                'person_id' => $this->person->id,
                'name'      => 'agent_notif.no_allow_set_email',
                'value_str' => '1',
            ];
        }
        if ($this->person->getPref('agent_notif.no_allow_set_browser')) {
            $pref_records[] = [
                'person_id' => $this->person->id,
                'name'      => 'agent_notif.no_allow_set_browser',
                'value_str' => '1',
            ];
        }

        //------------------------------
        // Save
        //------------------------------

        $this->db->beginTransaction();

        try {
            $this->db->delete('ticket_filter_subscriptions', ['person_id' => $this->person->id]);
            $this->db->executeUpdate("
                DELETE FROM people_prefs
                WHERE name LIKE 'agent_notif.%'
                AND person_id = ?
            ", [$this->person->id]);

            if ($pref_records || $filter_subs) {
                if ($filter_subs) {
                    foreach ($filter_subs as $s) {
                        $this->em->persist($s);
                    }
                }

                if ($pref_records) {
                    $this->db->batchInsert('people_prefs', $pref_records, true);
                }

                $this->em->flush();
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}
