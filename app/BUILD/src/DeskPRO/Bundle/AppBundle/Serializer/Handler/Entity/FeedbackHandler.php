<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\Feedback;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Feedback\Feedback as SerializedFeedback;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Feedback\FeedbackCsv;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use Doctrine\ORM\EntityManager;

/**
 * Class FeedbackHandler.
 */
class FeedbackHandler extends AbstractEntityHandler
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
        return Feedback::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param Feedback $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $serializerClass = $context->getMappedClass(Feedback::class);

        if ($serializerClass === FeedbackCsv::class) {
            return new FeedbackCsv($entity);
        }

        $this->ids[] = $entity->getId();

        $entity = new SerializedFeedback($entity);
        $entity->setCommentsCount(new CallbackDeferredProperty([$this, 'getCommentsCount'], [$entity]));

        return $entity;
    }

    /**
     * @internal
     *
     * @param SerializedFeedback $model
     *
     * @return int
     */
    public function getCommentsCount(SerializedFeedback $model)
    {
        if (!$model->getId()) {
            return 0;
        }

        if (!isset($this->commentCounts[$model->getId()])) {
            $qb = $this->em->createQueryBuilder();
            $qb
                ->select('count(c.id) as value', 'f.id')
                ->from(Feedback::class, 'f')
                ->leftJoin('f.comments', 'c')
                ->where('f.id IN (:ids)')
                ->setParameter('ids', $this->ids)
                ->groupBy('f.id');

            $result = $qb->getQuery()->getResult();
            foreach ($result as $count) {
                $this->commentCounts[$count['id']] = $count['value'];
            }
        }

        return $this->commentCounts[$model->getId()];
    }
}
