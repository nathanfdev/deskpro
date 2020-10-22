<?php

namespace Application\AgentBundle\Form\Type;

use Application\AgentBundle\Form\Model\NewTopic as NewTopicModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

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
            ->add('status', TextType::class)
            ->add('slug', TextType::class)
            ->add('type', ChoiceType::class, [
                'choices_as_values' => true,
                'choices'           => ['volume', 'chapter', 'page'],
                'empty_data'        => 'page',
            ])
            ->add('attach', CollectionType::class, [
                'type'         => 'hidden',
                'required'     => false,
                'allow_add'    => true,
                'allow_delete' => true,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onAddParentField']);
    }

    public function onAddParentField(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        $type = isset($data['type']) ? $data['type'] : null;

        switch ($type) {
            case 'volume':
                break;
            case 'chapter':
                $form->add('parent_id', TextType ::class);

                break;
            case 'page':
                $form->add('parent_id', TextType ::class, [
                    'required'    => true,
                    'constraints' => [
                        new Assert\NotNull(),
                        new Assert\NotBlank(),
                    ],
                ]);

                break;
        }
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
