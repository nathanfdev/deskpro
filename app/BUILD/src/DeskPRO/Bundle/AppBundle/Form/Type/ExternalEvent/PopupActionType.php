<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\ExternalEvent;

use DeskPRO\Bundle\AppBundle\Model\PopupModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class PopupActionType.
 */
class PopupActionType extends AbstractType
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
                    PopupModel::ACTION_TYPE_DISMISS,
                    PopupModel::ACTION_TYPE_TICKET,
                    PopupModel::ACTION_TYPE_WEBHOOK,
                ],
            ])
            ->add('title', TextType::class, [
                'required' => true,

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
        if (isset($data['type']) && $data['type'] === PopupModel::ACTION_TYPE_WEBHOOK) {
            // for now conditions to find target and additional info just arrays
                $form->add('webhook', WebhookType::class, ['required' => true]);
        } elseif (isset($data['type'])) {
            $form
                // for now conditions to find target and additional info just arrays

            ;
        }
    }
}
