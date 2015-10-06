<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
 */
namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Feedback;
use Orb\Util\Arrays;

/**
 * Sends feedback notifications to users with subscriptions.
 */
class FeedbackSubscriptions extends AbstractJob
{
    const DEFAULT_INTERVAL = 7200;

    public function run()
    {
        $last_time = App::getSetting('user.feedback_subscriptions_last');

        App::getDb()->replace('settings', array(
            'name'  => 'user.feedback_subscriptions_last',
            'value' => time(),
        ));

        if (!App::getSetting('user.feedback_subscriptions')) {
            return;
        }

        if (!$last_time) {
            return;
        }

        $last_date = new \DateTime("@$last_time");

        #------------------------------
        # Find Feedback
        #------------------------------

        $updated = App::getOrm()->createQuery('
            SELECT f
            FROM DeskPRO:Feedback f INDEX BY f.id
            LEFT JOIN f.category cat
            WHERE f.status IN (:statuses) AND (f.date_updated > :date OR f.date_last_comment > :date)
            ORDER BY f.date_updated DESC
        ')->setMaxResults(250)->execute(array('date' => $last_date, 'statuses' => array(Feedback::STATUS_ACTIVE, Feedback::STATUS_CLOSED)));

        if (!$updated) {
            return;
        }

        #------------------------------
        # Get subscriptions
        #------------------------------

        $structure = App::getContainer()->getSystemService('publish_structure');
        $helper    = $structure->getFeedbackCategoryHelper();

        $feedback_ids = array();

        foreach ($updated as $a) {
            $feedback_ids[] = $a->getId();
        }

        $feedback_ids  = array_unique($feedback_ids);
        $feedback_subs = array();

        if ($feedback_ids) {
            $feedback_subs = App::getDb()->fetchAllGrouped('
                SELECT person_id, feedback_id
                FROM feedback_subscriptions
                WHERE feedback_id IN (?)
            ', array($feedback_ids), 'person_id', null, 'feedback_id', array(Connection::PARAM_INT_ARRAY));
        }

        #------------------------------
        # Sort subscriptions into users
        #------------------------------

        $user_to_feedback = array();

        foreach ($feedback_subs as $person_id => $aids) {
            foreach ($aids as $aid) {
                if (!isset($updated[$aid])) {
                    continue;
                }

                if (!isset($user_to_feedback[$person_id])) {
                    $user_to_feedback[$person_id] = array();
                }
                $user_to_feedback[$person_id][$aid] = $updated[$aid];
            }
        }

        if (!$user_to_feedback) {
            return;
        }

        #------------------------------
        # Verify permissions
        #------------------------------

        $user_groupmembers = App::getDb()->fetchAllGrouped('
            SELECT person_id, usergroup_id
            FROM person2usergroups
            WHERE person_id IN (?)
        ', array(array_keys($user_to_feedback)), 'person_id', null, 'usergroup_id', array(Connection::PARAM_INT_ARRAY));

        $cat_groups = App::getDb()->fetchAllGrouped('
            SELECT category_id, usergroup_id
            FROM feedback_category2usergroup
        ', array(), 'category_id', null, 'usergroup_id');

        $all_user_to_feedback = $user_to_feedback;
        $user_to_feedback     = array();

        foreach ($all_user_to_feedback as $person_id => $feedbacks) {
            $person_ugs   = isset($user_groupmembers[$person_id]) ? $user_groupmembers[$person_id] : array();
            $person_ugs[] = 1; // Everyone

            foreach ($feedbacks as $fback) {
                $add     = false;
                $cat     = $fback->category;
                $cat_ugs = isset($cat_groups[$cat->getId()]) ? $cat_groups[$cat->getId()] : array();
                if (Arrays::isIn($person_ugs, $cat_ugs)) {
                    $add = true;
                }

                if ($add) {
                    if (!isset($user_to_feedback[$person_id])) {
                        $user_to_feedback[$person_id] = array();
                    }
                    $user_to_feedback[$person_id][$fback->getId()] = $fback;
                }
            }
        }

        unset($all_user_to_feedback);

        #------------------------------
        # Now send the emails (they are queued)
        #------------------------------

        foreach ($user_to_feedback as $person_id => $feedbacks) {
            $person = App::getOrm()->find('DeskPRO:Person', $person_id);
            if (!$person) {
                continue;
            }

            $updated_items = array();

            foreach ($feedbacks as $ff) {
                $updated_items[] = $ff;
            }

            $message = App::getMailer()->createMessage();
            $message->setToPerson($person);
            $message->setTemplate('DeskPRO:emails_user:feedback-subscription.html.twig', array(
                'person'        => $person,
                'updated_items' => $updated_items,
            ));

            App::getMailer()->send($message);

            // Saves mem
            App::getOrm()->detach($person);
        }

        if ($user_to_feedback) {
            $this->logStatus('Send '.count($user_to_feedback).' notifications');
        }
    }
}
