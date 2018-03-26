<?php

/**
 * DeskPRO.
 *
 * @category Mail
 */

namespace Application\DeskPRO\People;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManager;

class PersonEditManager implements PersonContextInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    protected $db;

    /**
     * Who is performing these edits.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person_context;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->db = $em->getConnection();
    }

    public function deleteUser(Person $person)
    {
        $purger = new Purger($person, $this->em);
        if ($this->person_context) {
            $purger->setPersonContext($this->person_context);
        }
        $purger->purge();
    }

    public function mergeUsers(Person $person, Person $other_person)
    {
    }

    /**
     * Save general notification preferences.
     *
     * $prefs is an array(pref=>true, pref=>true)
     *
     *
     * @param \Application\DeskPRO\Entity\Person $person
     * @param array                              $prefs
     *
     * @throws \Exception
     */
    public function saveNotificationPreferences(Person $person, array $prefs)
    {
        $valid_names = [
            'chat_message.email',
            'task_assign_self.email', 'task_assign_self.alert',
            'task_assign_team.email', 'task_assign_team.alert',
            'task_complete.email', 'task_complete.alert',
            'task_due.email', 'task_due.alert',
            'tweet_assign_self.email', 'tweet_assign_self.alert',
            'tweet_assign_team.email', 'tweet_assign_team.alert',
            'tweet_reply.email', 'tweet_reply.alert',
            'tweet_new_dm.email', 'tweet_new_dm.alert',
            'tweet_new_reply.email', 'tweet_new_reply.alert',
            'tweet_new_mention.email', 'tweet_new_mention.alert',
            'tweet_new_retweet.email', 'tweet_new_retweet.alert',
            'new_feedback.email', 'new_feedback.alert',
            'new_feedback_validate.email', 'new_feedback_validate.alert',
            'new_comment.email', 'new_comment.alert',
            'new_comment_validate.email', 'new_comment_validate.alert',
            'new_user.email', 'new_user.alert',
            'login_attempt.email', 'login_attempt_fail.email',
        ];

        $this->em->beginTransaction();

        $new_prefs = [];

        try {
            // Clear out old preferences
            $this->db->executeUpdate("
                DELETE FROM people_prefs
                WHERE person_id = ? AND `name` LIKE 'agent_notif.%' AND `name` NOT IN ('agent_notif.no_allow_set_email', 'agent_notif.no_allow_set_browser')
            ", [$person->id]);

            // Rebuild new ones
            foreach ($prefs as $name => $checked) {
                if (!$checked || !in_array($name, $valid_names)) {
                    continue;
                }

                $pref            = new \Application\DeskPRO\Entity\PersonPref();
                $pref->person    = $person;
                $pref->name      = "agent_notif.{$name}";
                $pref->value_str = '1';

                $this->em->persist($pref);

                $new_prefs[] = $pref;
            }

            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    /**
     * Save subscriptions on a filter. Only agents.
     *
     * $subs is array(filter_id => array(type=>true, type=>true, type=>true)
     *
     *
     * @param \Application\DeskPRO\Entity\Person $person
     * @param array                              $subs
     *
     * @throws \Exception
     *
     * @return array
     */
    public function saveFilterSubscriptions(Person $person, array $subs)
    {
        if (DP_INTERFACE != 'admin') {
            if ($person->getPref('agent_notif.no_allow_set_email') && $person->getPref('agent_notif.no_allow_set_browser')) {
                return [];
            }
        }

        $valid_names = [
            'email_created', 'email_new', 'email_leave', 'email_user_activity', 'email_agent_activity', 'email_agent_note', 'email_property_change',
            'alert_created', 'alert_new', 'alert_leave', 'alert_user_activity', 'alert_agent_activity', 'alert_agent_note', 'alert_property_change',
        ];

        $filter_info = App::getApi('tickets.filters')->getGroupedFiltersForPerson($person);

        $new_subs = [];

        // First delete all the ones the user has now, we're just gonna rebuild
        $this->db->delete('ticket_filter_subscriptions', ['person_id' => $person->id]);

        $current = $this->db->fetchAllKeyed('SELECT * FROM ticket_filter_subscriptions WHERE person_id = ?', [$person->id], 'filter_id');

        foreach ($filter_info['all_filters'] as $filter) {
            if (!isset($subs[$filter->id])) {
                $subs[$filter->id] = [];
            }

            $props = [];
            foreach ($valid_names as $k) {
                if (isset($subs[$filter->id][$k]) && $subs[$filter->id][$k]) {
                    $props[$k] = true;
                }
            }

            if (DP_INTERFACE != 'admin') {
                if ($person->getPref('agent_notif.no_allow_set_email')) {
                    foreach ($valid_names as $k) {
                        if (strpos($k, 'email_') !== 0) {
                            continue;
                        }
                        if (isset($current[$filter->id]) && $current[$filter->id][$k]) {
                            $props[$k] = true;
                        } else {
                            unset($props[$k]);
                        }
                    }
                } elseif ($person->getPref('agent_notif.no_allow_set_browser')) {
                    foreach ($valid_names as $k) {
                        if (strpos($k, 'alert_') !== 0) {
                            continue;
                        }
                        if (isset($current[$filter->id]) && $current[$filter->id][$k]) {
                            $props[$k] = true;
                        } else {
                            unset($props[$k]);
                        }
                    }
                }
            }

            if ($props) {
                $sub         = new \Application\DeskPRO\Entity\TicketFilterSubscription();
                $sub->filter = $filter;
                $sub->person = $person;

                foreach ($props as $k => $v) {
                    $sub->$k = $v;
                }

                $new_subs[] = $sub;

                $this->em->persist($sub);
            }
        }

        $this->em->flush();

        return $new_subs;
    }

    /**
     * Set the context (who is making these edits).
     *
     * @param Person $person
     */
    public function setPersonContext(Person $person)
    {
        $this->person_context = $person;
    }

    /**
     * @return \Application\DeskPRO\Entity\Person
     */
    public function getPersonContext()
    {
        return $this->person_context;
    }
}
