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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketLogs;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketLog;
use DeskPRO\Bundle\AppBundle\Form\Type\HtmlTextareaType;
use DeskPRO\Bundle\AppBundle\Form\Type\JsonArrayType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class TicketLogType.
 */
class TicketLogType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('details', JsonArrayType::class, [
                'required' => false,
            ])
            ->add('message_html', HtmlTextareaType::class, [
                'mapped'   => false,
                'required' => false,
            ])
            ->add('message_text', TextareaType::class, [
                'mapped'   => false,
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onMergeMessage']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSetRelations']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onValidateMessage']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => TicketLog::class,
            ])
            ->setRequired(['ticket', 'person'])
            ->setAllowedTypes('ticket', Ticket::class)
            ->setAllowedTypes('person', Person::class)
        ;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onMergeMessage(FormEvent $event)
    {
        $data = $event->getData();
        if (!isset($data['details'])) {
            $data['details'] = [];
        }
        if (!is_array($data['details'])) {
            return;
        }

        if (isset($data['message_html'])) {
            $data['details']['message_html'] = $data['message_html'];
        } elseif (isset($data['message_text'])) {
            $data['details']['message'] = $data['message_text'];
        }

        $event->setData($data);
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
        if (!$data instanceof TicketLog) {
            return;
        }

        $options = $form->getConfig()->getOptions();

        $data->setPerson($options['person']);
        $data->setTicket($options['ticket']);
        $data->setActionType('free');
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onValidateMessage(FormEvent $event)
    {
        $form = $event->getForm();
        if (!$form->get('message_html')->getData() && !$form->get('message_text')->getData()) {
            $form->get('message_html')->addError(new FormError(NotBlank::IS_BLANK_ERROR));
        }
    }
}
