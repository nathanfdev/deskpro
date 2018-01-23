<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Controller\Webhooks;

use Application\DeskPRO\Entity\TicketTrigger;
use DeskPRO\Bundle\AppBundle\Entity\Webhooks\TicketWebhook;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class TicketWebhookFormType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'mapped'   => true,
            ])
            ->add('payload_decoder', TextType::class, [
                'label'    => '',
                'required' => true,
                'mapped'   => true,
            ])
            ->add('is_enabled', CheckboxType::class, [
                'label'    => '',
                'required' => true,
                'mapped'   => true,
            ])
            ->add('search_terms', FilterTermsFormType::class, [
                'label'    => '',
                'required' => true,
                'mapped'   => true,
            ])
           ->add('triggers', CollectionType::class, [
               'mapped'        => true,
               'allow_add'    => true,
               'allow_delete' => true,
               'entry_type'    => TriggerFormType::class,
               'entry_options' => [],
           ])
          ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit'])
        ;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        /** @var TicketWebhook $data */
        $webhook = $form->getData();

        /** @var TicketTrigger $trigger */
        foreach ($webhook->getTriggers() as $trigger) {
            $this->setRequiredTriggerProperties($webhook, $trigger);
        }
    }

    /**
     * @param TicketWebhook $webhook
     * @param TicketTrigger $trigger
     */
    private function setRequiredTriggerProperties( TicketWebhook $webhook, TicketTrigger $trigger)
    {
        $trigger->event_trigger = TicketTrigger::EVENT_TYPE_WEBHOOK;
        $trigger->has_stop_triggers_action = false;
        $trigger->has_delete_ticket_action = false;
        $trigger->email_account = null;
        $trigger->by_agent_mode = null;
        $trigger->by_user_mode = null;
        $trigger->by_app_mode = null;

        $trigger->is_enabled = $webhook->isIsEnabled();

        if (! $trigger->title) {
            $trigger->title = sprintf('Trigger for webhook %s', $webhook->getAuthId());
        }
    }
}
