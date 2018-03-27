<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Reports\Form\Type;

use Application\DeskPRO\Entity\ReportBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportPropsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text', ['required' => true]);
        $builder->add('description', 'text', ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                 'data_class' => ReportBuilder::class,
            ]
        );
    }

    public function getName()
    {
        return 'report';
    }
}
