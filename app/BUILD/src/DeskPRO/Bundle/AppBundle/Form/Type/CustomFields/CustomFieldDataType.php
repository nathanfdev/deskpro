<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\CustomFields;

use Application\DeskPRO\Entity\CustomDefAbstract;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Form\Type\JsonArrayType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CustomFieldDefinitionType.
 */
class CustomFieldDataType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', TextType::class);
        $builder->add('description', TextareaType::class);
        $builder->add('handler_class', TextType::class);
        $builder->add('parent_id', TextType ::class);
        $builder->add('default_value', TextType::class);
        $builder->add('is_enabled', ApiBooleanType::class);
        $builder->add('options', JsonArrayType::class);
        $builder->add('aliases', TextType::class);
        $builder->add('is_user_enabled', ApiBooleanType::class);
        $builder->add('is_agent_field', ApiBooleanType::class);
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
