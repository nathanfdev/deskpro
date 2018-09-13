<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\ExternalEvent;

use DeskPRO\Bundle\AppBundle\Model\PopupModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PopupDisplayType.
 */
class PopupDisplayType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'required'          => true,
                'choices_as_values' => true,
                'choices'           => [
                    PopupModel::DISPLAY_BLOCK_TYPE_HTML,
                    PopupModel::DISPLAY_BLOCK_TYPE_PERSON,
                    PopupModel::DISPLAY_BLOCK_TYPE_ORG,
                    PopupModel::DISPLAY_BLOCK_TYPE_TICKET,
                ],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'finishForm']);
    }

    /**
     * @param FormEvent $event
     */
    public function finishForm(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        if (isset($data['type']) && $data['type'] === PopupModel::DISPLAY_BLOCK_TYPE_HTML) {
            // for now conditions to find target and additional info just arrays
                $form->add('content', TextType::class, ['required' => true]);
        } elseif (isset($data['type'])) {
            $form
                ->add('query', CollectionType::class, [
                    'entry_type'     => PopupQueryType::class,
                    'allow_add'      => true,
                    'allow_delete'   => true,
                    'error_bubbling' => false,
                    'required'       => true,
                    'constraints'    => [
                        new Assert\NotBlank(),
                    ],
                ])
                ->add('display', ChoiceType::class, [
                    'choices_as_values' => true,
                    'choices'           => [
                        PopupModel::DISPLAY_VIEW_TYPE_DETAIL,
                        PopupModel::DISPLAY_VIEW_TYPE_LIST,
                    ],
                ])
            ;
        }
    }
}
