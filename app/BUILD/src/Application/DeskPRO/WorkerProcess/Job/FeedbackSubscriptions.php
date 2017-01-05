<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\Person;
use Orb\Util\Arrays;

/**
 * Sends feedback notifications to users with subscriptions.
 */
class FeedbackSubscriptions extends AbstractJob
{
    const DEFAULT_INTERVAL = 7200;

    public function run()
    {
        $lastTime = $this->getContainer()->getSetting('user.feedback_subscriptions_last');

        $this->getContainer()->getDb()->replace('settings', [
            'name'  => 'user.feedback_subscriptions_last',
            'value' => time(),
        ]);

        if (!$this->getCrossBrandSetting('user.feedback_subscriptions')) {
            return;
        }

        if (!$lastTime) {
            return;
        }

        $lastDate = new \DateTime("@$lastTime");

        //------------------------------
        // Find Feedback
        //------------------------------

        /** @var Feedback[] $published */
        $published = $this->getContainer()->getEm()->createQuery('
            SELECT f
            FROM DeskPRO:Feedback f INDEX BY f.id
            WHERE f.status IN (:statuses) AND f.date_published > :date
            ORDER BY f.date_published DESC
        ')->setMaxResults(250)->execute(['date' => $lastDate, 'statuses' => [
            Feedback::STATUS_ACTIVE,
            Feedback::STATUS_CLOSED,
        ]]);

        /** @var Feedback[] $updated */
        $updated = $this->getContainer()->getEm()->createQuery('
            SELECT f
            FROM DeskPRO:Feedback f INDEX BY f.id
            WHERE f.status IN (:statuses) AND (f.date_updated > :date OR f.date_last_comment > :date)
            ORDER BY f.date_updated DESC
        ')->setMaxResults(250)->execute(['date' => $lastDate, 'statuses' => [
            Feedback::STATUS_ACTIVE,
            Feedback::STATUS_CLOSED,
        ]]);

        if (!$updated && !$published) {
            $this->logStatus('No new news feedbacks');

            return;
        }

        //------------------------------
        // Get subscriptions
        //------------------------------
        $publishedFeedbackIds = [];
        $updatedFeedbackIds   = [];

        foreach ($published as $a) {
            $publishedFeedbackIds[] = $a->getId();
        }
        foreach ($updated as $a) {
            $updatedFeedbackIds[] = $a->getId();
        }

        $publishedFeedbackIds = array_unique($publishedFeedbackIds);
        $updatedFeedbackIds   = array_unique($updatedFeedbackIds);

        $rootSubs     = [];
        $feedbackSubs = [];

        if ($publishedFeedbackIds) {
            $rootSubs = $this->getContainer()->getDb()->fetchAllGrouped('
                SELECT person_id, root_category
                FROM feedback_subscriptions
                WHERE root_category = 1
            ', [], 'person_id', null, 'root_category', [Connection::PARAM_INT_ARRAY]);
        }

        if ($updatedFeedbackIds) {
            $feedbackSubs = $this->getContainer()->getDb()->fetchAllGrouped('
                SELECT person_id, feedback_id
                FROM feedback_subscriptions
                WHERE feedback_id IN (?)
            ', [$updatedFeedbackIds], 'person_id', null, 'feedback_id', [Connection::PARAM_INT_ARRAY]);
        }

        //------------------------------
        // Sort subscriptions into users
        //------------------------------

        $userToFeedback = [];

        foreach ($rootSubs as $personId => $root) {
            foreach ($published as $feedback) {
                $userToFeedback[$personId][$feedback->getId()] = $feedback;
            }
        }

        foreach ($feedbackSubs as $personId => $aids) {
            foreach ($aids as $aid) {
                if (!isset($updated[$aid])) {
                    continue;
                }

                if (!isset($userToFeedback[$personId])) {
                    $userToFeedback[$personId] = [];
                }
                $userToFeedback[$personId][$aid] = $updated[$aid];
            }
        }

        if (!$userToFeedback) {
            return;
        }

        //------------------------------
        // Verify permissions
        //------------------------------

        $userGroupMembers = $this->getContainer()->getDb()->fetchAllGrouped('
            SELECT person_id, usergroup_id
            FROM person2usergroups
            WHERE person_id IN (?)
        ', [array_keys($userToFeedback)], 'person_id', null, 'usergroup_id', [Connection::PARAM_INT_ARRAY]);

        $catGroups = $this->getContainer()->getDb()->fetchAllGrouped('
            SELECT category_id, usergroup_id
            FROM feedback_category2usergroup
        ', [], 'category_id', null, 'usergroup_id');

        $allUserToFeedback = $userToFeedback;
        $userToFeedback    = [];

        foreach ($allUserToFeedback as $personId => $feedbacks) {
            $personUgs   = isset($userGroupMembers[$personId]) ? $userGroupMembers[$personId] : [];
            $personUgs[] = 1; // Everyone

            /** @var Feedback $feedback */
            foreach ($feedbacks as $feedback) {
                $add    = false;
                $cat    = $feedback->getCategory();
                $catUgs = isset($catGroups[$cat->getId()]) ? $catGroups[$cat->getId()] : [];
                if (Arrays::isIn($personUgs, $catUgs)) {
                    $add = true;
                }

                if ($add) {
                    if (!isset($userToFeedback[$personId])) {
                        $userToFeedback[$personId] = [];
                    }
                    $userToFeedback[$personId][$feedback->getId()] = $feedback;
                }
            }
        }

        unset($allUserToFeedback);

        //------------------------------
        // Now send the emails (they are queued)
        //------------------------------

        foreach ($userToFeedback as $personId => $feedbacks) {
            /** @var Person $person */
            $person = $this->getContainer()->getEm()->find(Person::class, $personId);
            if (!$person) {
                continue;
            }

            $updatedItems = [];

            foreach ($feedbacks as $feedback) {
                $updatedItems[] = $feedback;
            }

            if ($this->getContainer()->get('deskpro.feature_flags')->hasFeature('new_email_templates')) {
                $viewModel = $this->getContainer()->get('email.user_viewmodel_factory')
                    ->createFeedbackSubscriptionModel($updatedItems);
                $this->getContainer()->get('email.email_sender')
                    ->send($viewModel, ['to' => $person]);
            } else {
                $message = $this->getContainer()->getMailer()->createMessage();
                $message->setToPerson($person);
                $message->setTemplate(
                    'DeskPRO:emails_user:feedback-subscription.html.twig',
                    [
                        'person'        => $person,
                        'updated_items' => $updatedItems,
                    ]
                );

                $this->getContainer()->getMailer()->send($message);
            }

            // Saves mem
            $this->getContainer()->getEm()->detach($person);
        }

        if ($userToFeedback) {
            $this->logStatus('Send '.count($userToFeedback).' notifications');
        }
    }
}
