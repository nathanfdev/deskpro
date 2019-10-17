<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataService\Community;

use Application\DeskPRO\Entity\CommunityForum;
use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\Entity\CommunityTopicComment;
use Application\DeskPRO\Entity\CommunityTopicStatusCategory;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Rating;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\DataService\AbstractDataService;
use DeskPRO\Bundle\AppBundle\Security\Permissions\PermissionsManager;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentRatingsVoter;
use DeskPRO\Bundle\PortalBundle\Model\CommunityFilter;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CommunityDataService extends AbstractDataService
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var PermissionsManager
     */
    protected $permissions_manager;
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorization_checker;

    public function __construct(EntityManager $em, PermissionsManager $permissionsManager, AuthorizationCheckerInterface $authorizationChecker)
    {
        parent::__construct($em);
        $this->permissions_manager = $permissionsManager;
        $this->authorization_checker = $authorizationChecker;
    }

    /**
     * @return bool
     */
    public function hasAny()
    {
        $em = $this->em;

        return $this->generateAndCache(
            ['hasAny'],
            function () use ($em) {
                return $em->getConnection()->fetchColumn('SELECT COUNT(*) FROM community_topics LIMIT 1') ? true :
                    false;
            }
        );
    }

    /**
     * @param Pagerfanta $pager
     * @param int $activityCount
     * @return array
     */
    public function getLatestActivityForEachTopicInPager(Pagerfanta $pager, $activityCount)
    {
        $topicIds = array_map(function (CommunityTopic $topic) {
            return $topic->getId();
        }, iterator_to_array($pager));

        if (empty($topicIds)) {
            return [];
        }

        return $this->generateAndCache([__FUNCTION__, $topicIds, $activityCount], function () use ($topicIds, $activityCount) {
            $selects = array_map(function ($id) use ($activityCount) {
                return sprintf('(SELECT id FROM community_topic_comments WHERE topic_id = %d ORDER BY date_created LIMIT %d)', $id, $activityCount);
            }, $topicIds);

            $commentIds = $this->em->getConnection()
                ->executeQuery(implode(' UNION ', $selects))
                ->fetchAll()
            ;

            $commentIds = array_map(function ($row) {
                return (int) $row['id'];
            }, $commentIds);

            $qb = $this->em->createQueryBuilder()
                ->select('t.id AS topic_id, c AS comment, p')
                ->from(CommunityTopicComment::class, 'c')
                ->innerJoin('c.topic', 't')
                ->innerJoin('c.person', 'p')
                ->andWhere('c.id IN (:commentIds)')
                ->setParameter('commentIds', $commentIds)
            ;

            return array_reduce(
                $qb->getQuery()->getResult(),
                function (array $collection, array $row) {
                    $collection[$row['topic_id']][] = $row['comment'];

                    return $collection;
                },
                []
            );
        });
    }

    /**
     * @param                 $page
     * @param                 $max_per_page
     * @param CommunityFilter $filter
     * @param Person          $person
     *
     * @return Pagerfanta
     */
    public function getItemsPager($page, $max_per_page, CommunityFilter $filter, Person $person = null)
    {
        $em                  = $this->em;
        $permissions_manager = $this->permissions_manager;

        return $this->generateAndCache(
            [
                'getItemsPager',
                (int) $page,
                (int) $max_per_page,
                $filter,
                $person,
            ],
            function () use ($em, $permissions_manager, $page, $max_per_page, $filter, $person) {
                $qb = $em->createQueryBuilder();
                $qb->select('ct')->from(CommunityTopic::class, 'ct');

                // we have to filter the user's requested types with what they
                // are allowed to access.
                $permissions_bag = $permissions_manager->getPortalPermissionsBag($person);
                $allowed_types = $permissions_bag->getAllowedCommunityForumIds();
                $requested_types = $filter->getTypes();
                $types = [];
                if (null === $requested_types) {
                    $types = $allowed_types;
                } elseif (count($requested_types)) {
                    foreach ($requested_types as $req_type) {
                        if (in_array($req_type, $allowed_types)) {
                            $types[] = $req_type;
                        }
                    }
                }

                $filter->setTypes($types);

                // end filter types

                // status
                // "all","active","closed"
                switch ($filter->getStatus()) {
                    case CommunityFilter::STATUS_ALL:
                        $valid_status = [CommunityTopic::STATUS_ACTIVE, CommunityTopic::STATUS_CLOSED];
                        break;
                    case CommunityFilter::STATUS_ACTIVE:
                        $valid_status = [CommunityTopic::STATUS_ACTIVE];
                        break;
                    case CommunityFilter::STATUS_CLOSED:
                        $valid_status = [CommunityTopic::STATUS_CLOSED];
                        break;
                    default:
                        $valid_status = [];
                }
                $qb->where('ct.status IN (:valid_status)')->setParameter('valid_status', $valid_status);

                // status_categories (community_topic->status_category)
                // array(6,1,4)
                if (count($status_categories = $filter->getStatusCategories())) {
                    $qb->andWhere('ct.status_category IN (:status_categories)')->setParameter(
                        'status_categories',
                        $status_categories
                    );
                }

                // types
                // array(1,3,5) $community_topic->forum
                if (count($types = $filter->getTypes())) {
                    $qb->andWhere('ct.forum IN (:types)')->setParameter('types', $types);
                }

                // Free text search
                if ($filter->getQ()) {
                    $qb
                        ->andWhere('ct.title LIKE :search OR ct.content LIKE :search')
                        ->setParameter('search', "%{$filter->getQ()}%")
                    ;
                }

                // Activity filters
                if (count($filter->getActivities()) && $person) {
                    $activitiesClauses = [];
                    foreach ($filter->getActivities() as $activity) {
                        switch ($activity) {
                            case CommunityFilter::ACTIVITY_VOTED:
                                $qb->leftJoin(
                                    Rating::class,
                                    'r',
                                    'WITH',
                                    'r.object_type = \'community\' AND r.object_id = ct.id'
                                );
                                $activitiesClauses[] ='r.person = :person';
                                break;
                            case CommunityFilter::ACTIVITY_CREATED:
                                $activitiesClauses[] = 'ct.person = :person';
                                break;
                            case CommunityFilter::ACTIVITY_COMMENTED:
                                $qb->leftJoin('ct.comments', 'c');
                                $activitiesClauses[] = 'c.person = :person';
                                break;
                        }
                    }

                    if (count($activitiesClauses)) {
                        $qb
                            ->andWhere(implode(' OR ', $activitiesClauses))
                            ->setParameter('person', $person)
                        ;
                    }
                }

                // sort
                // "date", "most-popular", "highest-rating", "most-discussed", "most-viewed"
                switch ($filter->getSort()) {
                    case CommunityFilter::SORT_POPULARITY:
                        $qb->orderBy('ct.total_rating*5/DATE_DIFF(CURRENT_TIMESTAMP(),ct.date_created)',
                            $filter->getSortDirection());
                        $qb->addOrderBy('ct.date_created',
                            $filter->getSortDirection());
                        break;
                    case CommunityFilter::SORT_RATING:
                        $qb->orderBy('ct.total_rating', $filter->getSortDirection());
                        break;
                    case CommunityFilter::SORT_COMMENTS:
                        $qb->orderBy('ct.num_comments', $filter->getSortDirection());
                        break;
                    case CommunityFilter::SORT_VIEWS:
                        $qb->orderBy('ct.view_count', $filter->getSortDirection());
                        break;
                    case CommunityFilter::SORT_STATUS_CHANGE:
                        $qb->andWhere('ct.status_category > 1');
                        $qb->orderBy('ct.date_updated', $filter->getSortDirection());
                        break;
                    default:
                        $qb->orderBy('ct.date_created', $filter->getSortDirection());
                }

                // sort direction
                // "desc" or "asc"

                $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
                $pager->setMaxPerPage($max_per_page);
                $pager->setCurrentPage($page);

                return $pager;
            }
        );
    }

    /**
     * @param int|null|CommunityTopic $item
     *
     * @return null|CommunityTopic
     */
    public function getItem($item)
    {
        $that = $this;

        return $this->generateAndCache(
            [
                'getItem',
                $item,
            ],
            function () use ($that, $item) {
                if (!$item) { // we need some input
                    return;
                }

                if ($item instanceof CommunityTopic) { // already have what you seek
                    return $item;
                }

                return $that->getItemsRepo()->find($item);
            }
        );
    }

    public function getItemComments($item, Person $person = null)
    {
        $that = $this;

        return $this->generateAndCache(
            [
                'getItemComments',
                $item,
                $person,
            ],
            function () use ($that, $item, $person) {
                $item = $that->getItem($item);

                return $that->getItemCommentRepo()->getDisplayComments($item, $person);
            }
        );
    }

    /**
     * @param Person $person
     *
     * @return CommunityForum[]
     */
    public function getCommunityForumsForPerson(Person $person = null)
    {
        $permissions_bag = $this->permissions_manager->getPortalPermissionsBag($person);

        return $this->getCommunityForumsRepo()->findBy(
            [
                'id' => $permissions_bag->getAllowedCommunityForumIds(),
            ]
        );
    }

    /**
     * @param Person|null $person
     * @return array
     */
    public function getCommunityForumTopicCountsForPerson(Person $person = null)
    {
        $permissions_bag = $this->permissions_manager->getPortalPermissionsBag($person);

        return $this->getCommunityForumsRepo()->getTopicCountPerForum(
            $permissions_bag->getAllowedCommunityForumIds()
        );
    }

    /**
     * @param Person|null $person
     * @return array
     */
    public function getLatestCommentsPerForum(Person $person = null)
    {
        $permissions_bag = $this->permissions_manager->getPortalPermissionsBag($person);

        return $this->getCommunityForumsRepo()->getLatestCommentsPerForum(
            $permissions_bag->getAllowedCommunityForumIds()
        );
    }

    /**
     * @param array $options
     * @param Person|null $user
     * @return array
     */
    public function getFilteredTopicList(array $options, Person $user = null)
    {
        $person = $user ?: new PersonGuest();

        $filter = new CommunityFilter([
            'view'              => isset($options['view']) ? $options['view'] : null,
            'status'            => $options['status'],
            'status_categories' => $options['status_categories'],
            'types'             => $options['types'],
            'sort'              => $options['sort'],
            'sort_direction'    => $options['sort_direction'],
            'q'                 => $options['q'],
            'activities'        => isset($options['activities']) ? $options['activities'] : null,
            'view_mode'         => isset($options['view_mode']) ? $options['view_mode'] : null,
        ]);

        $pager = $this->getItemsPager(
            (int) $options['page'],
            (int) $options['count'],
            $filter,
            $person
        );

        foreach ($pager as $topic) {
            $topic->can_rate = $this
                ->authorization_checker
                ->isGranted(ContentRatingsVoter::RATE_COMMUNITY, $topic)
            ;
        }

        $types = $filter->getTypes();

        $allowed = $this
            ->permissions_manager
            ->getPortalPermissionsBag($user)
            ->getAllowedCommunityForumIds()
        ;

        $activitiesPerTopic = $this->getLatestActivityForEachTopicInPager(
            $pager,
            5
        );

        return [
            'pager'                => $pager,
            'filtered'             => (count($allowed) - count(($types)) > 0),
            'activities_per_topic' => $activitiesPerTopic,
        ];
    }

    /**
     * @param $status_category
     *
     * @return CommunityTopicStatusCategory
     */
    public function getCommunityTopicStatusCategory($status_category)
    {
        if ($status_category instanceof CommunityTopicStatusCategory) {
            return $status_category;
        }

        return $this->getCommunityTopicStatusCategoryRepo()->find($status_category);
    }

    /**
     * @param string $type status category type
     *
     * @return CommunityTopicStatusCategory
     */
    public function getCommunityFirstStatusCategoryByType($type = CommunityTopicStatusCategory::STATUS_ACTIVE)
    {
        return $this->getCommunityTopicStatusCategoryRepo()->findOneBy(['status_type' => $type], ['display_order' => 'ASC']);
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\CommunityTopic
     */
    public function getItemsRepo()
    {
        return $this->em->getRepository('DeskPRO:CommunityTopic');
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\CommunityTopicStatusCategory
     */
    public function getCommunityTopicStatusCategoryRepo()
    {
        return $this->em->getRepository('DeskPRO:CommunityTopicStatusCategory');
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\CommunityForum
     */
    public function getCommunityForumsRepo()
    {
        return $this->em->getRepository('DeskPRO:CommunityForum');
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\CommunityTopicComment
     */
    public function getItemCommentRepo()
    {
        return $this->em->getRepository('DeskPRO:CommunityTopicComment');
    }
}
