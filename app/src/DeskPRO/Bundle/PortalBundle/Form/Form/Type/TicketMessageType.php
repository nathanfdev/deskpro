<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;

class TicketMessageType extends AbstractType
{
    /**
     * @var LanguageManager
     */
    private $language_manager;

    public function __construct(LanguageManager $language_manager)
    {
        $this->language_manager = $language_manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (count($options['message_constraints'])) {
            $constraints = $options['message_constraints'];
        } else {
            $constraints = array(
                new NotNull(),
                new Length(array('min' => 10, 'minMessage' => 'Your message must be at least 10 characters in length.')),
            );
        }

        $builder->add('message_text', 'textarea', array(
            'label'       => $options['message_label'],
            'required'    => $options['required'],
            'attr'        => ['data-rte-field' => 'text'],
            'constraints' => $constraints,
        ));

        $builder->add('message_html', 'hidden', array(
            'attr'        => ['data-rte-field' => 'html'],
            'constraints' => $constraints,
        ));

        $builder->add('message_format', 'hidden', array(
            'data'   => 'text',
            'attr'   => ['data-rte-field' => 'format'],
            'mapped' => false,
        ));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreData'));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
    }

    public function onPreData(FormEvent $event)
    {
        /** @var \Application\DeskPRO\Entity\TicketMessage $message */
        $message = $event->getData();

        if (!$message) {
            $event->setData($message = new TicketMessage());
        }

        $config = $event->getForm()->getConfig();
        $ticket = $config->getOption('ticket');
        $person = $config->getOption('person');

        $message->setTicket($ticket);
        $message->setPerson($person);
    }

    public function onPreSubmit(FormEvent $event)
    {
        $messageData = $event->getData();
        $form        = $event->getForm();

        if ($messageData['message_format'] === 'text') {
            $form->remove('message_html');

            unset($messageData['message_html']);
            $event->setData($messageData);
        } else {
            if (array_key_exists('message_text', $messageData)) {
                $form->remove('message_text');

                // The actual content is always submitted in the text field,
                // so we need to swap that over to the html field so the
                // proper setter is called on TicketMessage
                $messageData['message_html'] = $messageData['message_text'];

                unset($messageData['message_text']);
                $event->setData($messageData);
            }
        }
    }

    public function getName()
    {
        return 'ticket_message';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'          => 'Application\\DeskPRO\\Entity\\TicketMessage',
            'message_label'       => $this->language_manager->phrase('portal.forms.label_message'),
            'message_constraints' => [],
            'attr'                => ['data-rte' => '1'],
        ));
        $resolver->setRequired(array(
            'person',
            'ticket',
        ));
        $resolver->setAllowedTypes(array(
            'person' => 'Application\\DeskPRO\\Entity\\Person',
            'ticket' => 'Application\\DeskPRO\\Entity\\Ticket',
        ));
    }
}
