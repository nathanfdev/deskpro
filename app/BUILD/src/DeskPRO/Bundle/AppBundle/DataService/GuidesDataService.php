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

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Topic;
use Application\DeskPRO\Entity\TopicComment;
use DeskPRO\Bundle\AppBundle\Security\Permissions\PermissionsManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class DownloadsDataService.
 */
class GuidesDataService extends AbstractDataService
{
    /**
     * @var PermissionsManager
     */
    protected $permissionsManager;

    /**
     * Constructor.
     *
     * @param EntityManager      $em
     * @param PermissionsManager $permissionsManager
     */
    public function __construct(EntityManager $em, PermissionsManager $permissionsManager)
    {
        parent::__construct($em);
        $this->permissionsManager = $permissionsManager;
    }

    /**
     * @return bool
     */
    public function hasAny()
    {
        $em = $this->em;

        return $this->generateAndCache(['hasAny'], function () use ($em) {
            return $em->getConnection()->fetchColumn('SELECT COUNT(*) FROM topics LIMIT 1') ? true : false;
        });
    }

    /**
     * @param $person
     *
     * @return Guide[]|null
     */
    public function getGuides($person)
    {
        return $this->generateAndCache(['getGuides', $person], function () use ($person) {
            $allowedIds = $this->permissionsManager->getPortalPermissionsBag(
                $person
            )->getAllowedGuides();

            return $this->getGuideRepo()->findBy(['id' => $allowedIds], ['display_order' => 'ASC']);
        });
    }

    /**
     * Takes a guide ID, or a Guide and returns an iterable collection of Topic.
     *
     * Null means return the roots.
     *
     * TODO: this is using the doctrine proxy as a method of finding children of the category. Might be able to improve that.
     *
     * @param int|null|Guide $guide
     * @param Person|null    $person
     *
     * @throws \InvalidArgumentException
     *
     * @return Topic[]
     */
    public function getGuideChildren($guide, $person)
    {
        return $this->generateAndCache(
            [
                'getGuideChildren',
                $guide,
                $person,
            ],
            function () use ($guide, $person) {
                $allowedIds = $this->permissionsManager->getPortalPermissionsBag(
                    $person
                )->getAllowedGuides();

                if (!in_array($guide->getId(), $allowedIds)) {
                    throw new AccessDeniedException('Unauthorized guide');
                }

                if (!$guide instanceof Guide) { // if not already category, try to make it one
                    if (!$guide = $this->getGuide($guide)) {
                        throw new \InvalidArgumentException(sprintf('could not convert "%s" into a guide'));
                    }
                }
                $result = $this->getTopicsRepo()
                    ->getInHierarchy(false, $guide);

                return $result;
            }
        );
    }

    /**
     * @param int|null|Topic $topic
     *
     * @return Topic|null
     */
    public function getTopic($topic)
    {
        return $this->generateAndCache(
            [
                'getTopic',
                $topic,
            ],
            function () use ($topic) {
                if (!$topic) { // we need some input
                    return;
                }

                if ($topic instanceof Topic) { // already have what you seek
                    return $topic;
                }

                return $this->getTopicsRepo()->find($topic);
            }
        );
    }

    /**
     * @param string $slug
     *
     * @return Topic|null
     */
    public function getTopicBySlug($slug)
    {
        return $this->generateAndCache(
            [
                'getTopicBySlug',
                $slug,
            ],
            function () use ($slug) {
                if (!$slug) { // we need some input
                    return;
                }

                return $this->getTopicsRepo()->findOneBy(['slug' => $slug]);
            }
        );
    }

    /**
     * @param string $slug
     *
     * @return Guide|null
     */
    public function getGuideBySlug($slug)
    {
        return $this->generateAndCache(
            [
                'getGuideBySlug',
                $slug,
            ],
            function () use ($slug) {
                if (!$slug) { // we need some input
                    return;
                }

                return $this->getGuideRepo()->findOneBy(['slug' => $slug]);
            }
        );
    }

    /**
     * Get a guide based on arbirtary input.
     *
     * TODO: optimize the heck out of any possible inputs here (if it helps: cache in an array at least, cache long term if desired, should normalize cache key on lowest common denominator "id")
     *
     * @param int|null|Guide $guide
     *
     * @return Guide|null
     */
    public function getGuide($guide)
    {
        return $this->generateAndCache(
            [
                'getGuide',
                $guide,
            ],
            function () use ($guide) {
                if (!$guide) { // we need some input
                    return;
                }

                if ($guide instanceof Guide) { // already have what you seek
                    return $guide;
                }

                return $this->getGuideRepo()->find($guide);
            }
        );
    }

    public function getTopicComments($topic, Person $person = null)
    {
        $that = $this;

        return $this->generateAndCache(
            [
                'getArticleComments',
                $topic,
                $person,
            ],
            function () use ($that, $topic, $person) {
                $topic = $that->getTopic($topic);

                return $that->getTopicCommentRepo()->getDisplayComments($topic, $person);
            }
        );
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\Topic
     */
    public function getTopicsRepo()
    {
        return $this->em->getRepository(Topic::class);
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\Guide
     */
    public function getGuideRepo()
    {
        return $this->em->getRepository(Guide::class);
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\TopicComment
     */
    public function getTopicCommentRepo()
    {
        return $this->em->getRepository(TopicComment::class);
    }
}
