<?php

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\Entity\Person;
use Orb\Util\Arrays;

/**
 * Sends community topics notifications to users with subscriptions.
 */
class CommunitySubscriptions extends AbstractJob
{
    const DEFAULT_INTERVAL = 7200;

    public function run()
    {
        $lastTime = $this->getContainer()->getSetting('user.community_topic_subscriptions_last');

        $this->getContainer()->getDb()->replace('settings', [
            'name'  => 'user.community_topic_subscriptions_last',
            'value' => time(),
        ]);

        if (!$this->getCrossBrandSetting('user.community_topic_subscriptions')) {
            return;
        }

        if (!$lastTime) {
            return;
        }

        $lastDate = new \DateTime("@$lastTime");

        //------------------------------
        // Find Community Topics
        //------------------------------

        /** @var CommunityTopic[] $published */
        $published = $this->getContainer()->getEm()->createQuery('
            SELECT ct
            FROM DeskPRO:CommunityTopic ct INDEX BY ct.id
            WHERE f.status IN (:statuses) AND ct.date_published > :date
            ORDER BY ct.date_published DESC
        ')->setMaxResults(250)->execute(['date' => $lastDate, 'statuses' => [
            CommunityTopic::STATUS_ACTIVE,
            CommunityTopic::STATUS_CLOSED,
        ]]);

        /** @var CommunityTopic[] $updated */
        $updated = $this->getContainer()->getEm()->createQuery('
            SELECT ct
            FROM DeskPRO:CommunityTopic f INDEX BY ct.id
            WHERE ct.status IN (:statuses) AND (ct.date_updated > :date OR ct.date_last_comment > :date)
            ORDER BY ct.date_updated DESC
        ')->setMaxResults(250)->execute(['date' => $lastDate, 'statuses' => [
            CommunityTopic::STATUS_ACTIVE,
            CommunityTopic::STATUS_CLOSED,
        ]]);

        if (!$updated && !$published) {
            $this->logStatus('No new community topics');

            return;
        }

        //------------------------------
        // Get subscriptions
        //------------------------------
        $publishedCommunityTopicIds = [];
        $updatedCommunityTopicIds   = [];

        foreach ($published as $a) {
            $publishedCommunityTopicIds[] = $a->getId();
        }
        foreach ($updated as $a) {
            $updatedCommunityTopicIds[] = $a->getId();
        }

        $publishedCommunityTopicIds = array_unique($publishedCommunityTopicIds);
        $updatedCommunityTopicIds   = array_unique($updatedCommunityTopicIds);

        $rootSubs  = [];
        $topicsubs = [];

        if ($publishedCommunityTopicIds) {
            $rootSubs = $this->getContainer()->getDb()->fetchAllGrouped('
                SELECT person_id, root_category
                FROM community_topic_subscriptions
                WHERE root_category = 1
                AND root_category_brand_id = :brand_id
            ', ['brand_id' => $brand->getId()], 'person_id', null, 'root_category');
        }

        if ($updatedCommunityTopicIds) {
            $topicsubs = $this->getContainer()->getDb()->fetchAllGrouped('
                SELECT person_id, topic_id
                FROM community_topic_subscriptions
                WHERE topic_id IN (?)
            ', [$updatedCommunityTopicIds], 'person_id', null, 'topic_id', [Connection::PARAM_INT_ARRAY]);
        }

        //------------------------------
        // Sort subscriptions into users
        //------------------------------

        $userToTopic = [];

        foreach ($rootSubs as $personId => $root) {
            foreach ($published as $topic) {
                $userToTopic[$personId][$topic->getId()] = $topic;
            }
        }

        foreach ($topicsubs as $personId => $aids) {
            foreach ($aids as $aid) {
                if (!isset($updated[$aid])) {
                    continue;
                }

                if (!isset($userToTopic[$personId])) {
                    $userToTopic[$personId] = [];
                }
                $userToTopic[$personId][$aid] = $updated[$aid];
            }
        }

        if (!$userToTopic) {
            return;
        }

        //------------------------------
        // Verify permissions
        //------------------------------

        $userGroupMembers = $this->getContainer()->getDb()->fetchAllGrouped('
            SELECT person_id, usergroup_id
            FROM person2usergroups
            WHERE person_id IN (?)
        ', [array_keys($userToTopic)], 'person_id', null, 'usergroup_id', [Connection::PARAM_INT_ARRAY]);

        $catGroups = $this->getContainer()->getDb()->fetchAllGrouped('
            SELECT category_id, usergroup_id
            FROM community_forum2usergroup
        ', [], 'forum_id', null, 'usergroup_id');

        $allUserToCommunityTopics = $userToTopic;
        $userToTopic              = [];

        foreach ($allUserToCommunityTopics as $personId => $topics) {
            $personUgs   = isset($userGroupMembers[$personId]) ? $userGroupMembers[$personId] : [];
            $personUgs[] = 1; // Everyone

            /** @var CommunityTopic $topic */
            foreach ($topics as $topic) {
                $add    = false;
                $cat    = $topic->getCategory();
                $catUgs = isset($catGroups[$cat->getId()]) ? $catGroups[$cat->getId()] : [];
                if (Arrays::isIn($personUgs, $catUgs)) {
                    $add = true;
                }

                if ($add) {
                    if (!isset($userToTopic[$personId])) {
                        $userToTopic[$personId] = [];
                    }
                    $userToTopic[$personId][$topic->getId()] = $topic;
                }
            }
        }

        unset($allUserToCommunityTopics);

        //------------------------------
        // Now send the emails (they are queued)
        //------------------------------

        foreach ($userToTopic as $personId => $topics) {
            /** @var Person $person */
            $person = $this->getContainer()->getEm()->find(Person::class, $personId);
            if (!$person) {
                continue;
            }

            $updatedItems = [];

            foreach ($topics as $topic) {
                $updatedItems[] = $topic;
            }

            if ($this->getContainer()->get('deskpro.feature_flags')->hasBeta('email_templates')) {
                $viewModel = $this->getContainer()->get('email.user_viewmodel_factory')
                    ->createCommunityTopicsSubscriptionModel($updatedItems);
                $this->getContainer()->get('email.email_sender')
                    ->send($viewModel, ['to' => $person]);
            } else {
                $message = $this->getContainer()->getMailer()->createMessage();
                $message->setToPerson($person);
                $message->setTemplate(
                    'DeskPRO:emails_user:community-topic-subscription.html.twig',
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

        if ($userToTopic) {
            $this->logStatus('Send '.count($userToTopic).' notifications');
        }
    }
}
