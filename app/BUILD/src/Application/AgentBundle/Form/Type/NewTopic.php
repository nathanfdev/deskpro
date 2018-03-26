<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Form\Type;

use Application\AgentBundle\Form\Model\NewTopic as NewTopicModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class NewTopic extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //------------------------------
        // Basic fields
        //------------------------------

        $builder
            ->add('title', TextType::class)
            ->add('content', TextareaType::class, ['filter_clean' => false])
            ->add('content_input', TextareaType::class, ['filter_clean' => false])
            ->add('content_input_type', TextType ::class)
            ->add('guide_id', TextType::class)
            ->add('parent_id', TextType ::class)
            ->add('status', TextType::class)
            ->add('slug', TextType::class)
            ->add('attach', CollectionType::class, [
                'type'         => 'hidden',
                'required'     => false,
                'allow_add'    => true,
                'allow_delete' => true,
            ])
        ;
    }

    public function getDefaultOptions(array $options)
    {
        return [
            'data_class' => NewTopicModel::class,
        ];
    }

    public function getName()
    {
        return 'newtopic';
    }
}
