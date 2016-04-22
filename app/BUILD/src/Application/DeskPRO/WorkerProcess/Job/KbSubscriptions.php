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
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Publish\Structure;
use Orb\Util\Arrays;

/**
 * Sends article and category notifications to users with subscriptions.
 */
class KbSubscriptions extends AbstractJob
{
    const DEFAULT_INTERVAL = 7200;

    public function run()
    {
        $lastTime = App::getSetting('user.kb_subscriptions_last');

        App::getDb()->replace('settings', [
            'name'  => 'user.kb_subscriptions_last',
            'value' => time(),
        ]);

        if (!App::getSetting('user.kb_subscriptions')) {
            return;
        }

        if (!$lastTime) {
            return;
        }

        $lastDate = new \DateTime("@$lastTime");

        #------------------------------
        # Find articles
        #------------------------------

        /** @var Article[] $published */
        $published = App::getOrm()->createQuery("
            SELECT a
            FROM DeskPRO:Article a INDEX BY a.id
            LEFT JOIN a.categories cat
            WHERE a.status = 'published' AND a.date_published > :date
            ORDER BY a.date_published DESC
        ")->setMaxResults(250)->execute(['date' => $lastDate]);

        /** @var Article[] $updated */
        $updated = App::getOrm()->createQuery("
            SELECT a
            FROM DeskPRO:Article a INDEX BY a.id
            LEFT JOIN a.categories cat
            WHERE a.status = 'published' AND (a.date_updated > :date OR a.date_last_comment > :date)
            ORDER BY a.date_updated DESC
        ")->setMaxResults(250)->execute(['date' => $lastDate]);

        if (!$published && !$updated) {
            $this->logStatus('No new articles');

            return;
        }
        #------------------------------
        # Get subscriptions
        #------------------------------

        /** @var Structure $structure */
        $structure = App::getContainer()->getSystemService('publish_structure');
        $helper    = $structure->getArticleCategoryHelper();

        $categoryIds = [];
        $articleIds  = [];

        foreach ($published as $a) {
            foreach ($a->getCategories() as $c) {
                $categoryIds[] = $c->getId();
            }
        }
        foreach ($updated as $a) {
            $articleIds[] = $a->getId();
        }

        $categoryIds = array_unique($categoryIds);
        $articleIds  = array_unique($articleIds);

        $catSubs     = [];
        $rootSubs    = [];
        $articleSubs = [];

        if ($categoryIds) {
            // Users can be subscribed to a category higher-up,
            // so for each article need to include subs for the whole path
            $add_ids = [];
            foreach ($categoryIds as $cid) {
                $parents = $helper->getPath(['id' => $cid]);
                foreach ($parents as $c) {
                    $add_ids[] = $c['id'];
                }
            }

            $categoryIds = array_merge($categoryIds, $add_ids);
            $categoryIds = array_unique($categoryIds);

            $catSubs = App::getDb()->fetchAllGrouped('
                SELECT person_id, category_id
                FROM kb_subscriptions
                WHERE category_id IN (?)
            ', [$categoryIds], 'person_id', null, 'category_id', [Connection::PARAM_INT_ARRAY]);

            $rootSubs = App::getDb()->fetchAllGrouped('
                SELECT person_id
                FROM kb_subscriptions
                WHERE root_category = 1
            ', [], 'person_id', null, 'root_category', [Connection::PARAM_INT_ARRAY]);
        }

        if ($articleIds) {
            $articleSubs = App::getDb()->fetchAllGrouped('
                SELECT person_id, article_id
                FROM kb_subscriptions
                WHERE article_id IN (?)
            ', [$articleIds], 'person_id', null, 'article_id', [Connection::PARAM_INT_ARRAY]);
        }

        #------------------------------
        # Sort subscriptions into users
        #------------------------------

        $userToArticles = [];

        foreach ($rootSubs as $personId => $root) {
            foreach ($published as $article) {
                $userToArticles[$personId][$article->getId()] = $article;
            }
        }

        foreach ($catSubs as $personId => $cids) {
            foreach ($published as $article) {
                foreach ($article->categories as $cat) {
                    $path   = $helper->getPathIds($cat);
                    $path[] = $cat->getId();

                    if (Arrays::isIn($path, $cids)) {
                        if (!isset($userToArticles[$personId])) {
                            $userToArticles[$personId] = [];
                        }
                        $userToArticles[$personId][$article->getId()] = $article;
                    }
                }
            }
        }

        foreach ($articleSubs as $personId => $aids) {
            foreach ($aids as $aid) {
                if (!isset($updated[$aid])) {
                    continue;
                }

                if (!isset($userToArticles[$personId])) {
                    $userToArticles[$personId] = [];
                }
                $userToArticles[$personId][$aid] = $updated[$aid];
            }
        }

        if (!$userToArticles) {
            return;
        }

        #------------------------------
        # Verify permissions
        #------------------------------

        $userGroupMembers = App::getDb()->fetchAllGrouped('
            SELECT person_id, usergroup_id
            FROM person2usergroups
            WHERE person_id IN (?)
        ', [array_keys($userToArticles)], 'person_id', null, 'usergroup_id', [Connection::PARAM_INT_ARRAY]);

        $catGroups = App::getDb()->fetchAllGrouped('
            SELECT category_id, usergroup_id
            FROM article_category2usergroup
        ', [], 'category_id', null, 'usergroup_id');

        $allUserToArticles = $userToArticles;
        $userToArticles    = [];

        foreach ($allUserToArticles as $personId => $articles) {
            $personUgs   = isset($userGroupMembers[$personId]) ? $userGroupMembers[$personId] : [];
            $personUgs[] = 1; // Everyone

            /** @var Article $article */
            foreach ($articles as $article) {
                $add = false;
                foreach ($article->getCategories() as $cat) {
                    $catUgs = isset($catGroups[$cat->getId()]) ? $catGroups[$cat->getId()] : [];
                    if (Arrays::isIn($personUgs, $catUgs)) {
                        $add = true;
                        break;
                    }
                }

                if ($add) {
                    if (!isset($userToArticles[$personId])) {
                        $userToArticles[$personId] = [];
                    }
                    $userToArticles[$personId][$article->getId()] = $article;
                }
            }
        }

        unset($allUserToArticles);

        #------------------------------
        # Now send the emails (they are queued)
        #------------------------------

        foreach ($userToArticles as $personId => $articles) {
            /** @var Person $person */
            $person = App::getOrm()->find('DeskPRO:Person', $personId);
            if (!$person) {
                continue;
            }

            $newArticles     = [];
            $updatedArticles = [];

            foreach ($articles as $article) {
                if ($article->getDatePublished() > $lastDate) {
                    $newArticles[] = $article;
                } else {
                    $updatedArticles[] = $article;
                }
            }

            $message = App::getMailer()->createMessage();
            $message->setToPerson($person);
            $message->setTemplate('DeskPRO:emails_user:kb-subscription.html.twig', [
                'person'           => $person,
                'new_articles'     => $newArticles,
                'updated_articles' => $updatedArticles,
                'unsub_auth'       => \Orb\Util\Util::generateStaticSecurityToken(App::getSetting('core.app_secret')
                    .$person->getId().$person->secret_string),
            ]);

            App::getMailer()->send($message);

            // Saves mem
            App::getOrm()->detach($person);
        }

        if ($userToArticles) {
            $this->logStatus('Send '.count($userToArticles).' notifications');
        }
    }
}
