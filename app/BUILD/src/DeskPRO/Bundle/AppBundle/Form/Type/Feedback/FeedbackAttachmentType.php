<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Feedback;

use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackAttachment;
use DeskPRO\Bundle\AppBundle\Form\Type\Attachments\WebAttachmentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FeedbackAttachmentType.
 */
class FeedbackAttachmentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSetRelations']);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return WebAttachmentType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'feedback_attachment';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => FeedbackAttachment::class,
            ])
            ->setRequired('feedback')
            ->setAllowedTypes('feedback', Feedback::class)
        ;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSetRelations(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        /** @var Feedback $feedback */
        $feedback = $form->getConfig()->getOption('feedback');
        if ($data instanceof FeedbackAttachment) {
            $feedback->addAttachment($data);
        }
    }
}
