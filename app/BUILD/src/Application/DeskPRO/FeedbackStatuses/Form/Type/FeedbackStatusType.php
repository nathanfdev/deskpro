<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\FeedbackStatuses\Form\Type;

use Application\DeskPRO\FeedbackStatuses\FeedbackStatusEdit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeedbackStatusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('feedback_status', new FeedbackStatusPropsType());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => FeedbackStatusEdit::class,
                'cascade_validation' => true,
            ]
        );
    }

    public function getName()
    {
        return 'feedback_status';
    }
}
