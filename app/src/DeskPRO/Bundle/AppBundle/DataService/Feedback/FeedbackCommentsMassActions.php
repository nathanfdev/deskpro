<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\DataService\Feedback;

use Application\DeskPRO\Entity\FeedbackComment;
use DeskPRO\Bundle\AppBundle\Data\MassActions\AbstractMassActionsPreprocessor;
use DeskPRO\Bundle\AppBundle\Data\MassActions\MassActionsPreprocessorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeedbackCommentsMassActions extends AbstractMassActionsPreprocessor implements MassActionsPreprocessorInterface
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['approve', 'delete']);
        $resolver->setAllowedValues('approve', function ($value) {
            return (int) $value === 1;
        });
        $resolver->setAllowedValues('delete', function ($value) {
            return (int) $value === 1;
        });
    }

    public function prepareActions()
    {
        return true;
    }

    public function selectEntities()
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('comment')
            ->from('DeskPRO:FeedbackComment', 'comment')
            ->where('comment.id IN (:ids)')
            ->setParameter('ids', $this->params['ids']);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param \Application\DeskPRO\Entity\FeedbackComment $comment
     */
    public function prepareEntity($comment)
    {
        foreach ($this->params['actions'] as $key => $value) {
            switch ($key) {
                case 'approve':
                    if ($value) {
                        $comment->setStatus(FeedbackComment::STATUS_VISIBLE);
                    }
                    break;
                case 'delete':
                    if ($value) {
                        $comment->setStatus(FeedbackComment::STATUS_DELETED);
                    }
                    break;
            }
        }
    }
}
