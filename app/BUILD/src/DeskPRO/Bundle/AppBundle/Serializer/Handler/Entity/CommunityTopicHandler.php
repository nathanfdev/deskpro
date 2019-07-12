<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\CommunityTopic;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Community\CommunityTopic as SerializedCommunityTopic;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Community\CommunityTopicCsv;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use Doctrine\ORM\EntityManager;

/**
 * Class CommunityTopicHandler.
 */
class CommunityTopicHandler extends AbstractEntityHandler
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var array
     */
    private $ids = [];

    /**
     * @var array
     */
    private $commentCounts = [];

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return CommunityTopic::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param CommunityTopic $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $serializerClass = $context->getMappedClass(CommunityTopic::class);

        if ($serializerClass === CommunityTopicCsv::class) {
            return new CommunityTopicCsv($entity);
        }

        $this->ids[] = $entity->getId();

        $entity = new SerializedCommunityTopic($entity);
        $entity->setCommentsCount(new CallbackDeferredProperty([$this, 'getCommentsCount'], [$entity]));

        return $entity;
    }

    /**
     * @internal
     *
     * @param SerializedCommunityTopic $model
     *
     * @return int
     */
    public function getCommentsCount(SerializedCommunityTopic $model)
    {
        if (!$model->getId()) {
            return 0;
        }

        if (!isset($this->commentCounts[$model->getId()])) {
            $qb = $this->em->createQueryBuilder();
            $qb
                ->select('count(c.id) as value', 'ct.id')
                ->from(CommunityTopic::class, 'ct')
                ->leftJoin('ct.comments', 'c')
                ->where('ct.id IN (:ids)')
                ->setParameter('ids', $this->ids)
                ->groupBy('ct.id');

            $result = $qb->getQuery()->getResult();
            foreach ($result as $count) {
                $this->commentCounts[$count['id']] = $count['value'];
            }
        }

        return $this->commentCounts[$model->getId()];
    }
}
