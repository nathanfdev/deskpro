<?php

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type\Api\Chat;

use Application\DeskPRO\Entity\ChatConversation;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ChatTranscriptToggleType.
 */
class ChatTranscriptToggleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('should_send_transcript', ApiBooleanType::class);
        $builder->get('should_send_transcript')->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onCheckPersonEmail']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection'               => false,
            'csrf_double_submit_protection' => false,
        ]);
    }

    /**
     * @param FormEvent $event
     */
    public function onCheckPersonEmail(FormEvent $event)
    {
        $form = $event->getForm();

        /** @var ChatConversation $conversation */
        $conversation = $form->getParent()->getData();
        if (!$conversation->getPersonEmail() && !$conversation->getPerson()) {
            $form->addError(new FormError('Person email is not defined.'));
        }
    }
}
