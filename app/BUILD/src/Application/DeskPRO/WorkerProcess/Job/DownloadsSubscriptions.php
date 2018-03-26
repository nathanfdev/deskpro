<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Publish\Structure;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
use Orb\Util\Arrays;

/**
 * Sends article and category notifications to users with subscriptions.
 */
class DownloadsSubscriptions extends AbstractJob
{
    const DEFAULT_INTERVAL = 7200;

    /**
     * @var Download[]
     */
    private $published;

    /**
     * @var Download[]
     */
    private $updated;

    public function run()
    {
        $lastTime = $this->getContainer()->getSetting('user.download_subscriptions_last');

        $this->getContainer()->getDb()->replace('settings', [
            'name'  => 'user.download_subscriptions_last',
            'value' => time(),
        ]);

        if (!$lastTime) {
            return;
        }

        $lastDate = new \DateTime("@$lastTime");

        /** @var Brand[] $brands */
        $brands = $this->getContainer()->getEm()->getRepository(Brand::class)->findAll();

        $brandStack = $this->getContainer()->getBrandStack();

        /** @var BrandAwareSettingsResolver $brandSettingsResolver */
        $brandSettingsResolver = $this->getContainer()->get('brand_aware_settings_resolver');

        foreach ($brands as $brand) {
            $brandStack->push($brand);

            if (!$brandSettingsResolver->getSetting('user.downloads_subscriptions')) {
                $brandStack->pop();
                continue;
            }

            $this->findDownloads($lastDate, $brand);

            if (!$this->published && !$this->updated) {
                $this->logStatus('No new downloads for '.$brand.' brand');
                $brandStack->pop();
                continue;
            }
            //------------------------------
            // Get subscriptions
            //------------------------------

            /** @var Structure $structure */
            $structure = $this->getContainer()->getSystemService('publish_structure');
            $helper    = $structure->getDownloadCategoryHelper();

            $categoryIds  = [];
            $downloadsIds = [];

            foreach ($this->published as $a) {
                $categoryIds[] = $a->getCategory()->getId();
            }
            foreach ($this->updated as $a) {
                $downloadsIds[] = $a->getId();
            }

            $categoryIds  = array_unique($categoryIds);
            $downloadsIds = array_unique($downloadsIds);

            $catSubs      = [];
            $rootSubs     = [];
            $downloadSubs = [];

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

                $catSubs = $this->getContainer()->getDb()->fetchAllGrouped('
                    SELECT person_id, category_id
                    FROM download_subscriptions
                    WHERE category_id IN (?)
                ', [$categoryIds], 'person_id', null, 'category_id', [Connection::PARAM_INT_ARRAY]);

                $rootSubs = $this->getContainer()->getDb()->fetchAllGrouped('
                    SELECT person_id, root_category
                    FROM download_subscriptions
                    WHERE root_category = 1
                ', [], 'person_id', null, 'root_category', [Connection::PARAM_INT_ARRAY]);
            }

            if ($downloadsIds) {
                $downloadSubs = $this->getContainer()->getDb()->fetchAllGrouped('
                    SELECT person_id, download_id
                    FROM download_subscriptions
                    WHERE download_id IN (?)
                ', [$downloadsIds], 'person_id', null, 'download_id', [Connection::PARAM_INT_ARRAY]);
            }

            //------------------------------
            // Sort subscriptions into users
            //------------------------------

            $userToDownloads = $this->sortSubscriptions($rootSubs, $catSubs, $downloadSubs, $helper);

            if (!$userToDownloads) {
                $brandStack->pop();
                continue;
            }

            //------------------------------
            // Verify permissions
            //------------------------------

            $userGroupMembers = $this->getContainer()->getDb()->fetchAllGrouped('
                SELECT person_id, usergroup_id
                FROM person2usergroups
                WHERE person_id IN (?)
            ', [array_keys($userToDownloads)], 'person_id', null, 'usergroup_id', [Connection::PARAM_INT_ARRAY]);

            $catGroups = $this->getContainer()->getDb()->fetchAllGrouped('
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

            $this->sendEmails($userToDownloads, $lastDate, $brand);

            $brandStack->pop();
        }
    }

    /**
     * @param \DateTime $lastDate
     * @param Brand     $brand
     */
    private function findDownloads($lastDate, $brand)
    {
        $this->published = $this->getContainer()->getEm()->createQuery("
            SELECT n
            FROM DeskPRO:Download n INDEX BY n.id
            JOIN n.category c
            WHERE n.status = 'published'
              AND n.date_published > :date
              AND c.brand = :brand
            ORDER BY n.date_published DESC
        ")->setMaxResults(250)->execute(['date' => $lastDate, 'brand' => $brand]);

        $this->updated = $this->getContainer()->getEm()->createQuery("
            SELECT n
            FROM DeskPRO:Download n INDEX BY n.id
            JOIN n.category c
            WHERE n.status = 'published'
              AND (n.date_updated > :date OR n.date_last_comment > :date)
              AND c.brand = :brand
            ORDER BY n.date_updated DESC
        ")->setMaxResults(250)->execute(['date' => $lastDate, 'brand' => $brand]);
    }

    /**
     * Sort subscriptions into users.
     *
     * @param array                        $rootSubs
     * @param array                        $catSubs
     * @param array                        $downloadSubs
     * @param \Orb\Util\HierarchyStructure $helper
     *
     * @return array
     */
    private function sortSubscriptions($rootSubs, $catSubs, $downloadSubs, $helper)
    {
        $userToDownloads = [];

        foreach ($rootSubs as $personId => $root) {
            foreach ($this->published as $download) {
                $userToDownloads[$personId][$download->getId()] = $download;
            }
        }

        foreach ($catSubs as $personId => $cids) {
            foreach ($this->published as $download) {
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

        foreach ($downloadSubs as $personId => $aids) {
            foreach ($aids as $aid) {
                if (!isset($this->updated[$aid])) {
                    continue;
                }

                if (!isset($userToDownloads[$personId])) {
                    $userToDownloads[$personId] = [];
                }
                $userToDownloads[$personId][$aid] = $this->updated[$aid];
            }
        }

        return $userToDownloads;
    }

    /**
     * Send the emails (they are queued).
     *
     * @param Download[][] $userToDownloads
     * @param \DateTime    $lastDate
     * @param Brand        $brand
     */
    private function sendEmails($userToDownloads, $lastDate, $brand)
    {
        foreach ($userToDownloads as $personId => $downloads) {

            /** @var Person $person */
            $person = $this->getContainer()->getEm()->find(Person::class, $personId);
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

            if ($this->getContainer()->get('deskpro.feature_flags')->hasBeta('email_templates')) {
                $viewModel = $this->getContainer()->get('email.user_viewmodel_factory')
                    ->createDownloadSubscriptionModel($newDownloads, $updatedDownloads);
                $this->getContainer()->get('email.email_sender')
                    ->send($viewModel, ['to' => $person]);
            } else {
                $message = $this->getContainer()->getMailer()->createMessage();
                $message->setToPerson($person);
                $message->setTemplate(
                    'DeskPRO:emails_user:download-subscription.html.twig',
                    [
                        'person'            => $person,
                        'new_downloads'     => $newDownloads,
                        'updated_downloads' => $updatedDownloads,
                    ]
                );

                $this->getContainer()->getMailer()->send($message);
            }

            // Saves mem
            $this->getContainer()->getEm()->detach($person);
        }

        if ($userToDownloads) {
            $this->logStatus('Sent '.count($userToDownloads).' notifications for '.$brand.' brand');
        }
    }
}
