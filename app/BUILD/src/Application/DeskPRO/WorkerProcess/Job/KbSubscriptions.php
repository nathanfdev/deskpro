<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Publish\Structure;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
use Orb\Util\Arrays;
use Orb\Util\Util;

/**
 * Sends article and category notifications to users with subscriptions.
 */
class KbSubscriptions extends AbstractJob
{
    const DEFAULT_INTERVAL = 7200;

    /**
     * @var Article[]
     */
    private $published;

    /**
     * @var Article[]
     */
    private $updated;

    public function run()
    {
        $lastTime = $this->getContainer()->getSetting('user.kb_subscriptions_last');

        $this->getContainer()->getDb()->replace('settings', [
            'name'  => 'user.kb_subscriptions_last',
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

            if (!$brandSettingsResolver->getSetting('user.kb_subscriptions')) {
                $brandStack->pop();
                continue;
            }

            $this->findArticles($lastDate, $brand);

            if (!$this->published && !$this->updated) {
                $this->logStatus('No new articles for '.$brand.' brand');
                $brandStack->pop();
                continue;
            }
            //------------------------------
            // Get subscriptions
            //------------------------------

            /** @var Structure $structure */
            $structure = $this->getContainer()->getSystemService('publish_structure');
            $helper    = $structure->getArticleCategoryHelper();

            $categoryIds = [];
            $articleIds  = [];

            foreach ($this->published as $a) {
                foreach ($a->getCategories() as $c) {
                    $categoryIds[] = $c->getId();
                }
            }
            foreach ($this->updated as $a) {
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

                $catSubs = $this->getContainer()->getDb()->fetchAllGrouped('
                    SELECT person_id, category_id
                    FROM kb_subscriptions
                    WHERE category_id IN (?)
                ', [$categoryIds], 'person_id', null, 'category_id', [Connection::PARAM_INT_ARRAY]);

                $rootSubs = $this->getContainer()->getDb()->fetchAllGrouped('
                    SELECT person_id, root_category
                    FROM kb_subscriptions
                    WHERE root_category = 1
                ', [], 'person_id', null, 'root_category', [Connection::PARAM_INT_ARRAY]);
            }

            if ($articleIds) {
                $articleSubs = $this->getContainer()->getDb()->fetchAllGrouped('
                    SELECT person_id, article_id
                    FROM kb_subscriptions
                    WHERE article_id IN (?)
                ', [$articleIds], 'person_id', null, 'article_id', [Connection::PARAM_INT_ARRAY]);
            }

            //------------------------------
            // Sort subscriptions into users
            //------------------------------

            $userToArticles = $this->sortSubscriptions($rootSubs, $catSubs, $articleSubs, $helper);

            if (!$userToArticles) {
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
            ', [array_keys($userToArticles)], 'person_id', null, 'usergroup_id', [Connection::PARAM_INT_ARRAY]);

            $catGroups = $this->getContainer()->getDb()->fetchAllGrouped('
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

            $this->sendEmails($userToArticles, $lastDate, $brand);

            $brandStack->pop();
        }
    }

    /**
     * @param \DateTime $lastDate
     * @param Brand     $brand
     */
    private function findArticles($lastDate, $brand)
    {
        $this->published = $this->getContainer()->getEm()->createQuery("
                SELECT a
                FROM DeskPRO:Article a INDEX BY a.id
                INNER JOIN a.categories cat
                WHERE a.status = 'published'
                  AND a.date_published > :date
                  AND cat.brand = :brand
                ORDER BY a.date_published DESC
            ")->setMaxResults(250)->execute(['date' => $lastDate, 'brand' => $brand]);

        $this->updated = $this->getContainer()->getEm()->createQuery("
                SELECT a
                FROM DeskPRO:Article a INDEX BY a.id
                INNER JOIN a.categories cat
                WHERE a.status = 'published'
                  AND (a.date_updated > :date OR a.date_last_comment > :date)
                  AND cat.brand = :brand
                ORDER BY a.date_updated DESC
            ")->setMaxResults(250)->execute(['date' => $lastDate, 'brand' => $brand]);
    }

    /**
     * @param array                        $rootSubs
     * @param array                        $catSubs
     * @param array                        $articleSubs
     * @param \Orb\Util\HierarchyStructure $helper
     *
     * @return array
     */
    private function sortSubscriptions($rootSubs, $catSubs, $articleSubs, $helper)
    {
        $userToArticles = [];

        foreach ($rootSubs as $personId => $root) {
            foreach ($this->published as $article) {
                $userToArticles[$personId][$article->getId()] = $article;
            }
        }

        foreach ($catSubs as $personId => $cids) {
            foreach ($this->published as $article) {
                foreach ($article->getCategories() as $cat) {
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
                if (!isset($this->updated[$aid])) {
                    continue;
                }

                if (!isset($userToArticles[$personId])) {
                    $userToArticles[$personId] = [];
                }
                $userToArticles[$personId][$aid] = $this->updated[$aid];
            }
        }

        return $userToArticles;
    }

    /**
     * @param Article[][] $userToArticles
     * @param \DateTime   $lastDate
     * @param Brand       $brand
     */
    private function sendEmails($userToArticles, $lastDate, $brand)
    {
        //------------------------------
        // Now send the emails (they are queued)
        //------------------------------

        foreach ($userToArticles as $personId => $articles) {
            /** @var Person $person */
            $person = $this->getContainer()->getEm()->find(Person::class, $personId);
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

            if ($this->getContainer()->get('deskpro.feature_flags')->hasBeta('email_templates')) {
                $viewModel = $this->getContainer()->get('email.user_viewmodel_factory')
                    ->createKbSubscriptionModel($newArticles, $updatedArticles);
                $this->getContainer()->get('email.email_sender')
                    ->send($viewModel, ['to' => $person]);
            } else {
                $message = $this->getContainer()->getMailer()->createMessage();
                $message->setToPerson($person);
                $message->setTemplate(
                    'DeskPRO:emails_user:kb-subscription.html.twig',
                    [
                        'person'           => $person,
                        'new_articles'     => $newArticles,
                        'updated_articles' => $updatedArticles,
                        'unsub_auth'       => Util::generateStaticSecurityToken(
                            $this->getContainer()->getSetting('core.app_secret').$person->getId().$person->secret_string
                        ),
                    ]
                );

                $this->getContainer()->getMailer()->send($message);
            }

            // Saves mem
            $this->getContainer()->getEm()->detach($person);
        }

        if ($userToArticles) {
            $this->logStatus('Sent '.count($userToArticles).' notifications for '.$brand.' brand');
        }
    }
}
