<?php

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Topic;
use Application\DeskPRO\Entity\TopicComment;
use DeskPRO\Bundle\AppBundle\Security\Permissions\PermissionsManager;
use Doctrine\DBAL\Connection;
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
     * @param mixed Person
     *
     * @return bool
     */
    public function hasAny($person)
    {
        return $this->generateAndCache(['hasAny'], function () use ($person) {
            $allowedIds = $this->permissionsManager->getPortalPermissionsBag(
                $person
            )->getAllowedGuides();

            if (!count($allowedIds)) {
                return false;
            }

            return (bool) $this->em->getConnection()->fetchColumn(
                'SELECT COUNT(*) FROM topics WHERE guide_id IN (?) AND `status` = ? LIMIT 1',
                [$allowedIds, Topic::STATUS_PUBLISHED], 0, [Connection::PARAM_INT_ARRAY, \PDO::PARAM_STR]
            );
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
