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
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Publish\Structure;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
use Orb\Util\Arrays;

/**
 * Sends news and category notifications to users with subscriptions.
 */
class NewsSubscriptions extends AbstractJob
{
    const DEFAULT_INTERVAL = 7200;

    /**
     * @var News[]
     */
    private $published;

    /**
     * @var News[]
     */
    private $updated;

    public function run()
    {
        $lastTime = $this->getContainer()->getSetting('user.news_subscriptions_last');

        $this->getContainer()->getDb()->replace('settings', [
            'name'  => 'user.news_subscriptions_last',
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

            if (!$brandSettingsResolver->getSetting('user.news_subscriptions')) {
                $brandStack->pop();
                continue;
            }

            $this->findNews($lastDate, $brand);

            if (!$this->published && !$this->updated) {
                $this->logStatus('No new news posts for '.$brand.' brand');
                $brandStack->pop();
                continue;
            }

            //------------------------------
            // Get subscriptions
            //------------------------------

            /** @var Structure $structure */
            $structure = $this->getContainer()->getSystemService('publish_structure');
            $helper    = $structure->getNewsCategoryHelper();

            $categoryIds = [];
            $newsIds     = [];

            foreach ($this->published as $a) {
                $categoryIds[] = $a->getCategory()->getId();
            }
            foreach ($this->updated as $a) {
                $newsIds[] = $a->getId();
            }

            $categoryIds = array_unique($categoryIds);
            $newsIds     = array_unique($newsIds);

            $catSubs  = [];
            $rootSubs = [];
            $newsSubs = [];

            if ($categoryIds) {
                // Users can be subscribed to a category higher-up,
                // so for each news need to include subs for the whole path
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
                    FROM news_subscriptions
                    WHERE category_id IN (?)
                ', [$categoryIds], 'person_id', null, 'category_id', [Connection::PARAM_INT_ARRAY]);

                $rootSubs = $this->getContainer()->getDb()->fetchAllGrouped('
                    SELECT person_id, root_category
                    FROM news_subscriptions
                    WHERE root_category = 1
                ', [], 'person_id', null, 'root_category', [Connection::PARAM_INT_ARRAY]);
            }

            if ($newsIds) {
                $newsSubs = $this->getContainer()->getDb()->fetchAllGrouped('
                    SELECT person_id, news_id
                    FROM news_subscriptions
                    WHERE news_id IN (?)
                ', [$newsIds], 'person_id', null, 'news_id', [Connection::PARAM_INT_ARRAY]);
            }

            //------------------------------
            // Sort subscriptions into users
            //------------------------------

            $userToNews = $this->sortSubscriptions($rootSubs, $catSubs, $newsSubs, $helper);

            if (!$userToNews) {
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
            ', [array_keys($userToNews)], 'person_id', null, 'usergroup_id', [Connection::PARAM_INT_ARRAY]);

            $catGroups = $this->getContainer()->getDb()->fetchAllGrouped('
                SELECT category_id, usergroup_id
                FROM news_category2usergroup
            ', [], 'category_id', null, 'usergroup_id');

            $allUserToNews = $userToNews;
            $userToNews    = [];

            foreach ($allUserToNews as $personId => $newsArray) {
                $personUgs   = isset($userGroupMembers[$personId]) ? $userGroupMembers[$personId] : [];
                $personUgs[] = 1; // Everyone

                /** @var News $news */
                foreach ($newsArray as $news) {
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

            unset($allUserToNews);

            $this->sendEmails($userToNews, $lastDate, $brand);

            $brandStack->pop();
        }
    }

    /**
     * @param \DateTime $lastDate
     * @param Brand     $brand
     */
    private function findNews($lastDate, $brand)
    {
        $this->published = $this->getContainer()->getEm()->createQuery("
                SELECT n
                FROM DeskPRO:News n INDEX BY n.id
                JOIN n.category c
                WHERE n.status = 'published'
                  AND n.date_published > :date
                  AND c.brand = :brand
                ORDER BY n.date_published DESC
            ")->setMaxResults(250)->execute(['date' => $lastDate, 'brand' => $brand]);

        // news does not update
        $this->updated = $this->getContainer()->getEm()->createQuery("
                SELECT n
                FROM DeskPRO:News n INDEX BY n.id
                JOIN n.category c
                WHERE n.status = 'published'
                  AND (n.date_last_comment > :date)
                  AND c.brand = :brand
                ORDER BY n.date_updated DESC
            ")->setMaxResults(250)->execute(['date' => $lastDate, 'brand' => $brand]);
    }

    /**
     * Sort subscriptions into users.
     *
     * @param array                        $rootSubs
     * @param array                        $catSubs
     * @param array                        $newsSubs
     * @param \Orb\Util\HierarchyStructure $helper
     *
     * @return array
     */
    private function sortSubscriptions($rootSubs, $catSubs, $newsSubs, $helper)
    {
        $userToNews = [];

        foreach ($rootSubs as $personId => $root) {
            foreach ($this->published as $news) {
                $userToNews[$personId][$news->getId()] = $news;
            }
        }

        foreach ($catSubs as $personId => $cids) {
            foreach ($this->published as $news) {
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

        foreach ($newsSubs as $personId => $aids) {
            foreach ($aids as $aid) {
                if (!isset($this->updated[$aid])) {
                    continue;
                }

                if (!isset($userToNews[$personId])) {
                    $userToNews[$personId] = [];
                }
                $userToNews[$personId][$aid] = $this->updated[$aid];
            }
        }

        return $userToNews;
    }

    /**
     * @param News[][]  $userToNews
     * @param \DateTime $lastDate
     * @param Brand     $brand
     */
    private function sendEmails($userToNews, $lastDate, $brand)
    {
        //------------------------------
        // Now send the emails (they are queued)
        //------------------------------

        foreach ($userToNews as $personId => $newsArray) {
            /** @var Person $person */
            $person = $this->getContainer()->getEm()->find(Person::class, $personId);
            if (!$person) {
                continue;
            }

            $newNews     = [];
            $updatedNews = [];

            foreach ($newsArray as $news) {
                if ($news->getDatePublished() > $lastDate) {
                    $newNews[] = $news;
                } else {
                    $updatedNews[] = $news;
                }
            }

            if ($this->getContainer()->get('deskpro.feature_flags')->hasBeta('email_templates')) {
                $viewModel = $this->getContainer()->get('email.user_viewmodel_factory')
                    ->createNewsSubscriptionModel($newNews, $updatedNews);
                $this->getContainer()->get('email.email_sender')
                    ->send($viewModel, ['to' => $person]);
            } else {
                $message = $this->getContainer()->getMailer()->createMessage();
                $message->setToPerson($person);
                $message->setTemplate(
                    'DeskPRO:emails_user:news-subscription.html.twig',
                    [
                        'person'       => $person,
                        'new_news'     => $newNews,
                        'updated_news' => $updatedNews,
                    ]
                );

                $this->getContainer()->getMailer()->send($message);
            }

            // Saves mem
            $this->getContainer()->getEm()->detach($person);
        }

        if ($userToNews) {
            $this->logStatus('Sent '.count($userToNews).' notifications for '.$brand.' brand');
        }
    }
}
