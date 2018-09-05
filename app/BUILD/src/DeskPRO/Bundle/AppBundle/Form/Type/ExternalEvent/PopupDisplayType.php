<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\ExternalEvent;

use DeskPRO\Bundle\AppBundle\Form\Type\JsonArrayType;
use DeskPRO\Bundle\AppBundle\Model\PopupModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

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
                // for now conditions to find target and additional info just arrays
                ->add('query', JsonArrayType::class, [
                    'required' => true,
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
