<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Zapier;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\ZapierHook;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonAssignType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class VoiceQueueType.
 */
class ZapierHookType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('target_url', TextType::class, [
                'required' => true,
            ])
            ->add('event', TextType::class, [
                'required' => true,
            ])
            ->add('subscription_url', TextType::class, [
                'mapped' => false,
            ])
            ->add('person', PersonAssignType::class, [
                'required' => true,
                'person'   => $options['person'],
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $hook = $event->getData();
                $form = $event->getForm();

                if ($hook['event'] === 'ticket_created') {
                    $form->add('params', ZapierTicketCreatedType::class, [
                        'mapped' => false,
                    ]);
                }
            })
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                /** @var ZapierHook $hook */
                $hook = $event->getData();
                $form = $event->getForm();

                if ($hook->getEvent() === 'ticket_created') {
                    $hook->setParams($form->get('params')->getData());
                }
            })
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('person')
            ->setDefaults([
                'data_class' => ZapierHook::class,
            ])
            ->setAllowedTypes('person', Person::class)
        ;
    }
}
