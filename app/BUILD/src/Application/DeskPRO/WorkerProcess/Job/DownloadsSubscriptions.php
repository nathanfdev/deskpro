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
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Publish\Structure;
use Orb\Util\Arrays;

/**
 * Sends article and category notifications to users with subscriptions.
 */
class DownloadsSubscriptions extends AbstractJob
{
    const DEFAULT_INTERVAL = 7200;

    public function run()
    {
        $lastTime = App::getSetting('user.download_subscriptions_last');

        App::getDb()->replace('settings', [
            'name'  => 'user.download_subscriptions_last',
            'value' => time(),
        ]);

        if (!App::getSetting('user.downloads_subscriptions')) {
            return;
        }

        if (!$lastTime) {
            return;
        }

        $lastDate = new \DateTime("@$lastTime");

        #------------------------------
        # Find articles
        #------------------------------

        /** @var Download[] $published */
        $published = App::getOrm()->createQuery("
            SELECT n
            FROM DeskPRO:Download n INDEX BY n.id
            JOIN n.category c
            WHERE n.status = 'published' AND n.date_published > :date
            ORDER BY n.date_published DESC
        ")->setMaxResults(250)->execute(['date' => $lastDate]);

        /** @var Download[] $updated */
        $updated = App::getOrm()->createQuery("
            SELECT n
            FROM DeskPRO:Download n INDEX BY n.id
            JOIN n.category c
            WHERE n.status = 'published' AND (n.date_updated > :date OR n.date_last_comment > :date)
            ORDER BY n.date_updated DESC
        ")->setMaxResults(250)->execute(['date' => $lastDate]);

        if (!$published && !$updated) {
            $this->logStatus('No new news downloads');

            return;
        }
        #------------------------------
        # Get subscriptions
        #------------------------------

        /** @var Structure $structure */
        $structure = App::getContainer()->getSystemService('publish_structure');
        $helper    = $structure->getDownloadCategoryHelper();

        $categoryIds  = [];
        $downloadsIds = [];

        foreach ($published as $a) {
            $categoryIds[] = $a->getCategory()->getId();
        }
        foreach ($updated as $a) {
            $downloadsIds[] = $a->getId();
        }

        $categoryIds  = array_unique($categoryIds);
        $downloadsIds = array_unique($downloadsIds);

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
                FROM download_subscriptions
                WHERE category_id IN (?)
            ', [$categoryIds], 'person_id', null, 'category_id', [Connection::PARAM_INT_ARRAY]);

            $rootSubs = App::getDb()->fetchAllGrouped('
                SELECT person_id
                FROM download_subscriptions
                WHERE root_category = 1
            ', [], 'person_id', null, 'root_category', [Connection::PARAM_INT_ARRAY]);
        }

        if ($downloadsIds) {
            $articleSubs = App::getDb()->fetchAllGrouped('
                SELECT person_id, download_id
                FROM download_subscriptions
                WHERE download_id IN (?)
            ', [$downloadsIds], 'person_id', null, 'download_id', [Connection::PARAM_INT_ARRAY]);
        }

        #------------------------------
        # Sort subscriptions into users
        #------------------------------

        $userToDownloads = [];

        foreach ($rootSubs as $personId => $root) {
            foreach ($published as $download) {
                $userToDownloads[$personId][$download->getId()] = $download;
            }
        }

        foreach ($catSubs as $personId => $cids) {
            foreach ($published as $download) {
                $cat    = $download->getCategory();
                $path   = $helper->getPathIds($cat);
                $path[] = $cat->getId();

                if (Arrays::isIn($path, $cids)) {
                    if (!isset($userToDownloads[$personId])) {
                        $userToDownloads[$personId] = [];
                    }
                    $userToDownloads[$personId][$download->getId()] = $download;
                }
            }
        }

        foreach ($articleSubs as $personId => $aids) {
            foreach ($aids as $aid) {
                if (!isset($updated[$aid])) {
                    continue;
                }

                if (!isset($userToDownloads[$personId])) {
                    $userToDownloads[$personId] = [];
                }
                $userToDownloads[$personId][$aid] = $updated[$aid];
            }
        }

        if (!$userToDownloads) {
            return;
        }

        #------------------------------
        # Verify permissions
        #------------------------------

        $userGroupMembers = App::getDb()->fetchAllGrouped('
            SELECT person_id, usergroup_id
            FROM person2usergroups
            WHERE person_id IN (?)
        ', [array_keys($userToDownloads)], 'person_id', null, 'usergroup_id', [Connection::PARAM_INT_ARRAY]);

        $catGroups = App::getDb()->fetchAllGrouped('
            SELECT category_id, usergroup_id
            FROM download_category2usergroup
        ', [], 'category_id', null, 'usergroup_id');

        $allUserToDownloads = $userToDownloads;
        $userToDownloads    = [];

        foreach ($allUserToDownloads as $personId => $downloads) {
            $personUgs   = isset($userGroupMembers[$personId]) ? $userGroupMembers[$personId] : [];
            $personUgs[] = 1; // Everyone

            /** @var Download $download */
            foreach ($downloads as $download) {
                $add    = false;
                $cat    = $download->getCategory();
                $catUgs = isset($catGroups[$cat->getId()]) ? $catGroups[$cat->getId()] : [];
                if (Arrays::isIn($personUgs, $catUgs)) {
                    $add = true;
                }

                if ($add) {
                    if (!isset($userToDownloads[$personId])) {
                        $userToDownloads[$personId] = [];
                    }
                    $userToDownloads[$personId][$download->getId()] = $download;
                }
            }
        }

        unset($allUserToDownloads);

        #------------------------------
        # Now send the emails (they are queued)
        #------------------------------

        foreach ($userToDownloads as $personId => $downloads) {
            //var_dump($person_id, $articles);exit;

            /** @var Person $person */
            $person = App::getOrm()->find('DeskPRO:Person', $personId);
            if (!$person) {
                continue;
            }

            $newDownloads     = [];
            $updatedDownloads = [];

            foreach ($downloads as $download) {
                if ($download->getDatePublished() > $lastDate) {
                    $newDownloads[] = $download;
                } else {
                    $updatedDownloads[] = $download;
                }
            }

            $message = App::getMailer()->createMessage();
            $message->setToPerson($person);
            $message->setTemplate('DeskPRO:emails_user:download-subscription.html.twig', [
                'person'            => $person,
                'new_downloads'     => $newDownloads,
                'updated_downloads' => $updatedDownloads,
            ]);

            App::getMailer()->send($message);

            // Saves mem
            App::getOrm()->detach($person);
        }

        if ($userToDownloads) {
            $this->logStatus('Send '.count($userToDownloads).' notifications');
        }
    }
}
