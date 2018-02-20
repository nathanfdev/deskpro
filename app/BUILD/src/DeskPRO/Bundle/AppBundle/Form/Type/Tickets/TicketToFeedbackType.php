<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Entity\TicketToFeedback;
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
 * Class TicketToFeedbackType.
 */
class TicketToFeedbackType extends AbstractType
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
                'data_class' => TicketToFeedback::class,
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
