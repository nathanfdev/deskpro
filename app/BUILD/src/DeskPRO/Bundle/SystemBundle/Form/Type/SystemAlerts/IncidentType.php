<?php

namespace DeskPRO\Bundle\SystemBundle\Form\Type\SystemAlerts;

use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ProblemType.
 */
class IncidentType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('dismissed', ApiBooleanType::class, [
            'required' => false,
        ]);
    }
}
