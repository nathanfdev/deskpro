<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Publish\Structure;
use Orb\Util\Arrays;

/**
 * Sends article and category notifications to users with subscriptions.
 */
class NewsSubscriptions extends AbstractJob
{
    const DEFAULT_INTERVAL = 7200;

    public function run()
    {
        $lastTime = App::getSetting('user.news_subscriptions_last');

        App::getDb()->replace('settings', [
            'name'  => 'user.news_subscriptions_last',
            'value' => time(),
        ]);

        if (!App::getSetting('user.news_subscriptions')) {
            return;
        }

        if (!$lastTime) {
            return;
        }

        $lastDate = new \DateTime("@$lastTime");

        #------------------------------
        # Find articles
        #------------------------------

        /** @var News[] $published */
        $published = App::getOrm()->createQuery("
            SELECT n
            FROM DeskPRO:News n INDEX BY n.id
            JOIN n.category c
            WHERE n.status = 'published' AND n.date_published > :date
            ORDER BY n.date_published DESC
        ")->setMaxResults(250)->execute(['date' => $lastDate]);

        // news does not update
        $updated = [];
        /** @var News[] $updated */
        $updated = App::getOrm()->createQuery("
            SELECT n
            FROM DeskPRO:News n INDEX BY n.id
            JOIN n.category c
            WHERE n.status = 'published' AND (n.date_last_comment > :date)
            ORDER BY n.date_updated DESC
        ")->setMaxResults(250)->execute(['date' => $lastDate]);

        if (!$published && !$updated) {
            $this->logStatus('No new news posts');

            return;
        }

        #------------------------------
        # Get subscriptions
        #------------------------------

        /** @var Structure $structure */
        $structure = App::getContainer()->getSystemService('publish_structure');
        $helper    = $structure->getNewsCategoryHelper();

        $categoryIds = [];
        $newsIds     = [];

        foreach ($published as $a) {
            $categoryIds[] = $a->getCategory()->getId();
        }
        foreach ($updated as $a) {
            $newsIds[] = $a->getId();
        }

        $categoryIds = array_unique($categoryIds);
        $newsIds     = array_unique($newsIds);

        $catSubs     = [];
        $rootSubs    = [];
        $articleSubs = [];

        if ($categoryIds) {
            // Users can be subscribed to a category higher-up,
            // so for each article need to include subs for the whole path
            $addIds = [];
            foreach ($categoryIds as $cid) {
                $parents = $helper->getPath(['id' => $cid]);
                foreach ($parents as $c) {
                    $addIds[] = $c['id'];
                }
            }

            $categoryIds = array_merge($categoryIds, $addIds);
            $categoryIds = array_unique($categoryIds);

            $catSubs = App::getDb()->fetchAllGrouped('
                SELECT person_id, category_id
                FROM news_subscriptions
                WHERE category_id IN (?)
            ', [$categoryIds], 'person_id', null, 'category_id', [Connection::PARAM_INT_ARRAY]);

            $rootSubs = App::getDb()->fetchAllGrouped('
                SELECT person_id
                FROM news_subscriptions
                WHERE root_category = 1
            ', [], 'person_id', null, 'root_category', [Connection::PARAM_INT_ARRAY]);
        }

        if ($newsIds) {
            $articleSubs = App::getDb()->fetchAllGrouped('
                SELECT person_id, news_id
                FROM news_subscriptions
                WHERE news_id IN (?)
            ', [$newsIds], 'person_id', null, 'news_id', [Connection::PARAM_INT_ARRAY]);
        }

        #------------------------------
        # Sort subscriptions into users
        #------------------------------

        $userToNews = [];

        foreach ($rootSubs as $personId => $root) {
            foreach ($published as $news) {
                $userToNews[$personId][$news->getId()] = $news;
            }
        }

        foreach ($catSubs as $personId => $cids) {
            foreach ($published as $news) {
                $cat    = $news->getCategory();
                $path   = $helper->getPathIds($cat);
                $path[] = $cat->getId();

                if (Arrays::isIn($path, $cids)) {
                    if (!isset($userToNews[$personId])) {
                        $userToNews[$personId] = [];
                    }
                    $userToNews[$personId][$news->getId()] = $news;
                }
            }
        }

        foreach ($articleSubs as $personId => $aids) {
            foreach ($aids as $aid) {
                if (!isset($updated[$aid])) {
                    continue;
                }

                if (!isset($userToNews[$personId])) {
                    $userToNews[$personId] = [];
                }
                $userToNews[$personId][$aid] = $updated[$aid];
            }
        }

        if (!$userToNews) {
            return;
        }

        #------------------------------
        # Verify permissions
        #------------------------------

        $userGroupMembers = App::getDb()->fetchAllGrouped('
            SELECT person_id, usergroup_id
            FROM person2usergroups
            WHERE person_id IN (?)
        ', [array_keys($userToNews)], 'person_id', null, 'usergroup_id', [Connection::PARAM_INT_ARRAY]);

        $catGroups = App::getDb()->fetchAllGrouped('
            SELECT category_id, usergroup_id
            FROM news_category2usergroup
        ', [], 'category_id', null, 'usergroup_id');

        $allUserToArticles = $userToNews;
        $userToNews        = [];

        foreach ($allUserToArticles as $personId => $articles) {
            $personUgs   = isset($userGroupMembers[$personId]) ? $userGroupMembers[$personId] : [];
            $personUgs[] = 1; // Everyone

            /** @var News $news */
            foreach ($articles as $news) {
                $add    = false;
                $cat    = $news->getCategory();
                $catUgs = isset($catGroups[$cat->getId()]) ? $catGroups[$cat->getId()] : [];
                if (Arrays::isIn($personUgs, $catUgs)) {
                    $add = true;
                }

                if ($add) {
                    if (!isset($userToNews[$personId])) {
                        $userToNews[$personId] = [];
                    }
                    $userToNews[$personId][$news->getId()] = $news;
                }
            }
        }

        unset($allUserToArticles);

        #------------------------------
        # Now send the emails (they are queued)
        #------------------------------

        foreach ($userToNews as $personId => $articles) {
            /** @var Person $person */
            $person = App::getOrm()->find('DeskPRO:Person', $personId);
            if (!$person) {
                continue;
            }

            $newArticles     = [];
            $updatedArticles = [];

            foreach ($articles as $news) {
                if ($news->getDatePublished() > $lastDate) {
                    $newArticles[] = $news;
                } else {
                    $updatedArticles[] = $news;
                }
            }

            $message = App::getMailer()->createMessage();
            $message->setToPerson($person);
            $message->setTemplate('DeskPRO:emails_user:news-subscription.html.twig', [
                    'person'           => $person,
                    'new_articles'     => $newArticles,
                    'updated_articles' => $updatedArticles, ]
            );

            App::getMailer()->send($message);

            // Saves mem
            App::getOrm()->detach($person);
        }

        if ($userToNews) {
            $this->logStatus('Send '.count($userToNews).' notifications');
        }
    }
}
