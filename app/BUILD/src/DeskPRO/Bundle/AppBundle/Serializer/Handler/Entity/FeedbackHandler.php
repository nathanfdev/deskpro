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
