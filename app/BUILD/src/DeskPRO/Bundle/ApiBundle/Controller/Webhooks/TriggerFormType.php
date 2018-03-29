<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Webhooks;

use Application\DeskPRO\Entity\TicketTrigger;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TriggerFormType extends AbstractType
{
    const OPTION_DEFAULT_TITLE = 'default_title';

    const OPTION_ENABLE_WEBHOOK_PROPS = 'webhook_props';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label'    => '',
                'required' => false,
                'mapped'   => true,
                'empty_data' => $options[TriggerFormType::OPTION_DEFAULT_TITLE]
            ])
            ->add('actions', TriggerActionsFormType::class, [
                'label'    => '',
                'required' => true,
                'mapped'   => true,
            ])
            ->add('terms', TriggerTermsFormType::class, [
                'label'    => '',
                'required' => false,
                'mapped'   => true,
            ])
        ;

        $builder->addEventListener(
            FormEvents::SUBMIT,
            function(FormEvent $event) use ($options) {
                $this->onSubmit($event, $options);
            },
            -1
        );
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event, $options = [])
    {
        $trigger = $event->getData();
        // in the event we reuse this form type we can switch off the
        if ($options[TriggerFormType::OPTION_ENABLE_WEBHOOK_PROPS]) {
            $trigger->event_trigger = TicketTrigger::EVENT_TYPE_WEBHOOK;
            $trigger->has_stop_triggers_action = false;
            $trigger->has_delete_ticket_action = false;
            $trigger->email_account = null;
            $trigger->by_agent_mode = null;
            $trigger->by_user_mode = null;
            $trigger->by_app_mode = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
            'data_class' => TicketTrigger::class,
            TriggerFormType::OPTION_DEFAULT_TITLE => '',
            TriggerFormType::OPTION_ENABLE_WEBHOOK_PROPS => false
        ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
            'data_class'         => TicketTrigger::class,
            TriggerFormType::OPTION_DEFAULT_TITLE => '',
            TriggerFormType::OPTION_ENABLE_WEBHOOK_PROPS => false
        ]);
    }
}
