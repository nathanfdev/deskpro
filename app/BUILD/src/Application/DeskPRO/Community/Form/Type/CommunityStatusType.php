<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Community\Form\Type;

use Application\DeskPRO\Community\CommunityStatusEdit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommunityStatusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('community_status', new CommunityStatusPropsType());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => CommunityStatusEdit::class,
                'cascade_validation' => true,
            ]
        );
    }

    public function getName()
    {
        return 'community_status';
    }
}
