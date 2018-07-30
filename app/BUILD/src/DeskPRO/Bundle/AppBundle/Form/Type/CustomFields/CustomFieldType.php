<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\CustomFields;

use Application\DeskPRO\Entity\CustomDefAbstract;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CustomFieldType.
 */
class CustomFieldType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('is_enabled', ApiBooleanType::class);
        $builder->add('display_order', NumberType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => CustomDefAbstract::class,
            'allow_extra_fields' => true,
        ]);
    }
}
