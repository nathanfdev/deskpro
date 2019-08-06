<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Community;

use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\Notifications\NewCommunityTopicNotification;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Community\CommunityTopicType;
use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * API access to community.
 *
 * @ApiModes("all")
 * @Rest\Route("/community_topics")
 * @ApiDoc(target="all", section="Community", output="DeskPRO\Bundle\AppBundle\Serializer\Model\Community\CommunityTopic")
 * @ApiDoc(
 *     target="listAction,countAction",
 *     filters={
 *         {"name"="awaiting_validation", "pattern"="1", "description"="select community topics awaiting validation only", "dataType"="boolean"},
 *         {"name"="status", "pattern"="active|closed|hidden", "description"="filter by status", "dataType"="string"},
 *         {"name"="hidden_status", "dataType"="integer", "pattern"="unpublished|deleted|spam|draft", "description"="limit with hidden_status"},
 *         {"name"="status_category", "pattern"="\w|[\w]", "description"="filter by status category", "dataType"="string[]"},
 *         {"name"="channel", "pattern"="\w|[\w]", "description"="channel title, or titles array", "dataType"="string[]"},
 *         {"name"="custom_channel", "pattern"="\w|[\w]", "description"="filter by custom channel", "dataType"="string[]"},
 *         {"name"="labels_mode", "pattern"="any|all", "description"="how to load labels", "dataType"="string"},
 *         {"name"="label", "pattern"="\w,\w...\w", "description"="select community topics with given lables", "dataType"="string"},
 *         {"name"="no_labels", "pattern"="1", "description"="select community topics have no label", "dataType"="boolean"},
 *         {"name"="ids", "pattern"="\d,\d...\d", "description"="comma separated ids list", "dataType"="string"},
 *         {"name"="created_from", "pattern"="YYYY-mm-dd H:i:s", "description"="limit by date, interval`s start", "dataType"="date"},
 *         {"name"="created_to", "pattern"="YYYY-mm-dd H:i:s", "description"="limit by date, interval`s end", "dataType"="date"},
 *         {
 *              "name"="community_topic_field.{id}",
 *              "description"="
 *                  Custom community topic field filter. To filter by a custom field with ID=1 you need to add
 *                  ?community_topic_field.1=value to the query string",
 *              "dataType"="string",
 *              "pattern"="\d+|\w"
 *          }
 *     }
 * )
 * @ApiDoc(
 *     target="listAction",
 *     filters={
 *         {"name"="order_by", "pattern"="date_created|total_rating|num_ratings|id|title|status|channel|person", "description"="how to order result", "dataType"="string"},
 *         {"name"="order_dir", "pattern"="asc|desc", "description"="order direction", "dataType"="string"}
 *     }
 * )
 * @ApiDoc(
 *     target="countAction",
 *     filters={
 *         {"name"="group_by", "pattern"="status_category|hidden_status|channel|custom_channel", "description"="how to group counts", "dataType"="boolean"}
 *     }
 * )
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Coomunity\CommunityTopicType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\CommunityTopic",
 *          "person"="Application\DeskPRO\Entity\Person"
 *      }
 *     }
 * )
 */
class CommunityTopicsController extends AbstractCommunityController
{
    public static $entity      = CommunityTopic::class;
    public static $type        = CommunityTopicType::class;
    public static $sortOptions = [
        'date_created' => 'date_created',
        'total_rating' => 'total_rating',
        'num_ratings'  => 'num_ratings',
        'title'        => 'title',
        'status'       => 'status',
        'channel'      => ['join' => 'channel', 'as' => 'c', 'sort' => 'c.id'],
        'person'       => ['join' => 'person', 'as' => 'p', 'sort' => 'p.id'],
    ];

    /**
     * Get data for export to CSV.
     *
     * @Rest\Get("/csv")
     * @SerializerView(mapping={
     *     "Application\DeskPRO\Entity\CommunityTopic": "DeskPRO\Bundle\AppBundle\Serializer\Model\Community\CommunityTopicCsv"
     * })
     *
     * @param Request $request
     *
     * @return \FOS\RestBundle\View\View
     */
    public function csvAction(Request $request)
    {
        return $this->listAction($request);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $this->applyNotReviewedFilters($qb, $alias, $request);
        $this->applyDateCreatedFilters($qb, $alias, $request);
        $this->applyCommunityListFilters($qb, $alias, $request);

        $qb->groupBy("$alias.id");
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListGroupBy(QueryBuilder $qb, $alias, $groupBy, Request $request)
    {
        switch ($groupBy) {
            case 'hidden_status':
                $qb
                    ->addSelect("{$alias}.hidden_status as group_name")
                    ->addSelect("{$alias}.hidden_status as title")
                    ->andWhere("{$alias}.hidden_status IS NOT NULL")
                    ->andWhere("{$alias}.hidden_status <> ''")
                    ->groupBy('group_name');

                break;
            case 'custom_channel':
                $qb
                    ->join("{$alias}.custom_data", 'customCat')
                    ->join('customCat.field', 'def')
                    ->join('def.parent', 'parent')
                    ->addSelect('def.title as title')
                    ->addSelect('def.id as group_name')
                    ->andWhere('parent.sys_name = :chan')
                    ->setParameter('chan', 'chan')
                    ->groupBy('group_name');

                break;
            case 'channel':
                $qb
                    ->join("{$alias}.channel", 'channel')
                    ->addSelect('channel.title as title')
                    ->addSelect('channel.id as group_name')
                    ->groupBy('group_name');

                break;
            case 'status_category':
                $qb
                    ->join("{$alias}.status_category", 'statusCategory')
                    ->addSelect('statusCategory.title as title')
                    ->addSelect('statusCategory.id as group_name')
                    ->groupBy('group_name');

                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'agent_interface' => true,
            'person'          => $this->getUser(),
        ]);

        $view = parent::handleForm($model, $request, $options);

        // if no exception has been thown when community topic has been created
        $notify = new NewCommunityTopicNotification($model);
        $notify->send();

        return $view;
    }
}
