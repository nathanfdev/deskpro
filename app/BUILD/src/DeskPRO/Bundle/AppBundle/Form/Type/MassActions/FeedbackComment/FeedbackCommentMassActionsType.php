<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\MassActions\FeedbackComment;

use DeskPRO\Bundle\AppBundle\Form\Type\MassActions\BaseMassActionsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FeedbackCommentMassActionsType.
 */
class FeedbackCommentMassActionsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return BaseMassActionsType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'params_class' => FeedbackCommentMassActionParamsType::class,
        ]);
    }
}
