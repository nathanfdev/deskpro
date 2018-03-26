<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Entity\TicketFeedbackLink;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class TicketFeedbackLinkType.
 */
class TicketFeedbackLinkType extends AbstractType
{
    /**
     * @var BrandAwareSettingsResolver
     */
    protected $settingsResolver;

    /**
     * Constructor.
     *
     * @param BrandAwareSettingsResolver $settingsResolver
     */
    public function __construct(BrandAwareSettingsResolver $settingsResolver)
    {
        $this->settingsResolver = $settingsResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('feedback', EntityType::class, [
                'class'    => Feedback::class,
                'required' => true,
            ])
            ->add('is_subscribe_ticket_owner', ApiBooleanType::class, [
                'required'    => false,
                'mapped'      => false,
                'constraints' => [
                    new Assert\Callback([$this, 'validateFeedbackSubscriptionEnabled']),
                ],
            ])
            ->add('is_subscribe_ticket_participants', ApiBooleanType::class, [
                'required'    => false,
                'mapped'      => false,
                'constraints' => [
                    new Assert\Callback([$this, 'validateFeedbackSubscriptionEnabled']),
                ],
            ]);

        $builder->addEventSubscriber(new TicketDisableAutoProcessListener());
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSetRelations'], 50);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit'], 100);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => TicketFeedbackLink::class,
                'ticket'     => null,
                'person'     => null,
            ])
            ->setRequired([
                'ticket',
                'person',
            ])
            ->setAllowedTypes('ticket', Ticket::class)
            ->setAllowedTypes('person', Person::class)
        ;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSetRelations(FormEvent $event)
    {
        $form   = $event->getForm();
        $data   = $event->getData();
        $config = $form->getConfig();

        /** @var Ticket $ticket */
        $ticket = $config->getOption('ticket');
        $ticket->addFeedbackLink($data);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $form->getData();

        $data->setTicket($form->getConfig()->getOption('ticket'));
        $data->setPerson($form->getConfig()->getOption('person'));
    }

    public function validateFeedbackSubscriptionEnabled($value, ExecutionContextInterface $context)
    {
        if ($value && !$this->settingsResolver->getSetting('user.feedback_subscriptions')) {
            $context->buildViolation('api.error_codes.option_depends_from_setting')
                ->setParameter('setting', 'user.feedback_subscriptions')
                ->setCode('option_depends_from_setting')
                ->addViolation();
        }
    }
}
