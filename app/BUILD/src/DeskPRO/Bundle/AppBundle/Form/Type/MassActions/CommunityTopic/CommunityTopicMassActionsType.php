<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\MassActions\CommunityTopic;

use DeskPRO\Bundle\AppBundle\Form\Type\MassActions\BaseMassActionsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CommunityTopicMassActionsType.
 */
class CommunityTopicMassActionsType extends AbstractType
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
            'params_class' => CommunityTopicMassActionParamsType::class,
        ]);
    }
}
