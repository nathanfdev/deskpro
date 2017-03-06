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

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Topic;
use Application\DeskPRO\Entity\TopicComment;
use DeskPRO\Bundle\AppBundle\Security\Permissions\PermissionsManager;
use DeskPRO\Component\Util\ListUtils;
use Doctrine\ORM\EntityManager;

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
        $that = $this;

        return $this->generateAndCache(['getGuides', $person], function () use ($that, $person) {
            $allowedIds = $that->permissionsManager->getPortalPermissionsBag(
                $person
            )->getAllowedGuides();

            return $that->getGuideRepo()->findBy(['id' => $allowedIds]);
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
     * @param Person         $person
     *
     * @throws \InvalidArgumentException
     *
     * @return Topic[]
     */
    public function getGuideChildren($guide, Person $person)
    {
        $that = $this;

        return $this->generateAndCache(
            [
                'getGuideChildren',
                $guide,
                $person,
            ],
            function () use ($that, $guide, $person) {
                $allowedIds = $that->permissionsManager->getPortalPermissionsBag(
                    $person
                )->getAllowedGuides();

                if (!in_array($guide->getId(), $allowedIds)) {
                    throw new \Exception('Unauthorized guide');
                }

                if (!$guide instanceof Guide) { // if not already category, try to make it one
                    if (!$guide = $that->getGuide($guide)) {
                        throw new \InvalidArgumentException(sprintf('could not convert "%s" into a guide'));
                    }
                }
                $result = $that->getTopicsRepo()
                    ->findBy([
                        'parent' => null,
                        'guide'  => $guide,
                    ]);

                $result = ListUtils::sortByFnValue($result, function ($topic) {
                    /* @var Topic $topic */
                    return $topic->getDisplayOrder();
                });

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
        $that = $this;

        return $this->generateAndCache(
            [
                'getTopic',
                $topic,
            ],
            function () use ($that, $topic) {
                if (!$topic) { // we need some input
                    return;
                }

                if ($topic instanceof Topic) { // already have what you seek
                    return $topic;
                }

                return $that->getTopicsRepo()->find($topic);
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
        $that = $this;

        return $this->generateAndCache(
            [
                'getGuide',
                $guide,
            ],
            function () use ($that, $guide) {
                if (!$guide) { // we need some input
                    return;
                }

                if ($guide instanceof Guide) { // already have what you seek
                    return $guide;
                }

                return $that->getGuideRepo()->find($guide);
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
