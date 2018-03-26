<?php

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type\Api\Chat;

use Application\DeskPRO\Entity\ChatConversation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ChatFeedbackType.
 */
class ChatFeedbackType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('helpful', NumberType::class, [
                'property_path' => 'rating_overall',
                'constraints'   => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('comment', TextType::class, [
                'property_path' => 'rating_comment',
                'required'      => false,
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onCheckEnded']);
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
    public function onCheckEnded(FormEvent $event)
    {
        /** @var ChatConversation $conversation */
        $conversation = $event->getData();
        if (!$conversation->getDateEnded()) {
            $event->getForm()->get('helpful')->addError(new FormError('Unable to send feedback, chat is not ended yet.'));
        }
    }
}
