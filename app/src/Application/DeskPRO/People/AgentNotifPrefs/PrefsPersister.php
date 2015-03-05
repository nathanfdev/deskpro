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
     * @param  Prefs      $prefs
     * @throws \Exception
     */
    public function savePrefs(Prefs $prefs)
    {
        $filters = $this->em->getRepository('DeskPRO:TicketFilter')->getFiltersForPerson($this->person);
        $filters = Arrays::keyFromData($filters, 'id');


        #------------------------------
        # Create sub records
        #------------------------------

        $filter_subs = array();

        $person = $this->person;
        $fn_get_sub = function ($filter_id) use (&$filter_subs, $person, $filters) {
            if (!isset($filter_subs[$filter_id])) {
                $filter_subs[$filter_id] = new TicketFilterSubscription();
                $filter_subs[$filter_id]->filter = $filters[$filter_id];
                $filter_subs[$filter_id]->person = $person;
            }

            return $filter_subs[$filter_id];
        };

        foreach ($prefs->getFilterSubs('email') as $filter_id => $subs) {
            $sub = $fn_get_sub($filter_id);

            foreach ($subs as $name => $v) {
                if (!$v) continue;
                $sub->{'email_' . $name} = true;
            }
        }
        foreach ($prefs->getFilterSubs('alert') as $filter_id => $subs) {
            $sub = $fn_get_sub($filter_id);

            foreach ($subs as $name => $v) {
                if (!$v) continue;
                $sub->{'alert_' . $name} = true;
            }
        }

        #------------------------------
        # Pref records
        #------------------------------

        $pref_records = array();

        foreach (array(
             'chat',
             'task',
             'twitter',
             'feedback',
             'publish',
             'crm',
             'account'
        ) as $app_name) {
            foreach (array('email', 'alert') as $type) {
                $subs = $prefs->getAppSubs($type, $app_name);
                foreach ($subs as $name => $v) {
                    if (!$v) continue;
                    $pref_records[] = array(
                        'person_id' => $this->person->id,
                        'name'      => "agent_notif.{$name}.$type",
                        'value_str' => '1',
                    );
                }
            }
        }

        // todo
        if ($this->person->getPref('agent_notif.no_allow_set_email')) {
            $pref_records[] = array(
                'person_id' => $this->person->id,
                'name'      => 'agent_notif.no_allow_set_email',
                'value_str' => '1',
            );
        }
        if ($this->person->getPref('agent_notif.no_allow_set_browser')) {
            $pref_records[] = array(
                'person_id' => $this->person->id,
                'name'      => 'agent_notif.no_allow_set_browser',
                'value_str' => '1',
            );
        }

        #------------------------------
        # Save
        #------------------------------

        $this->db->beginTransaction();

        try {
            $this->db->delete('ticket_filter_subscriptions', array('person_id' => $this->person->id));
            $this->db->executeUpdate("
                DELETE FROM people_prefs
                WHERE name LIKE 'agent_notif.%'
                AND person_id = ?
            ", array($this->person->id));

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
