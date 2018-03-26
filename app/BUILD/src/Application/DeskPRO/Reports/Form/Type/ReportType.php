<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Reports\Form\Type;

use Application\DeskPRO\Reports\ReportEdit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('report', new ReportPropsType());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => ReportEdit::class,
                'cascade_validation' => true,
            ]
        );
    }

    public function getName()
    {
        return 'report_edit';
    }
}
