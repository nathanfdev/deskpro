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
namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use Orb\Util\Strings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TicketDescriptionType.
 */
class TicketDescriptionType extends AbstractType
{
    /**
     * @var LanguageManager
     */
    private $language_manager;

    /**
     * Constructor.
     *
     * @param LanguageManager $language_manager
     */
    public function __construct(LanguageManager $language_manager)
    {
        $this->language_manager = $language_manager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (count($options['message_constraints'])) {
            $constraints = $options['message_constraints'];
        } else {
            $constraints = [
                new Assert\NotNull(['message' => 'portal.forms.error_ticket_msg_required']),
                new Assert\Length(['min' => 10, 'minMessage' => 'portal.forms.error_ticket_msg_length']),
            ];
        }

        // message_text and message_html are not mapped because
        // we manually call our setters onPostSubmit so we can
        // set text or html, depending on what the users browser submitted

        $builder
            ->add('message_text', 'textarea', [
                'label'       => $options['message_label'],
                'required'    => $options['required'],
                'attr'        => ['data-rte-field' => 'text'],
                'constraints' => $constraints,
                'mapped'      => false,
            ])
            ->add('message_html', 'html_textarea', [
                'attr'   => ['data-rte-field' => 'html', 'style' => 'display:none'], // style is hidden by default, we show with JS
                'label'  => false,
                'mapped' => false,
            ])
            ->add('message_format', 'hidden', [
                'data'        => 'text',
                'attr'        => ['data-rte-field' => 'format'],
                'constraints' => [
                    new Assert\Choice(['choices' => ['text', 'html']]),
                ],
                'mapped' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreData']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * @param FormEvent $event
     */
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

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $messageData = $event->getData();

        switch ($messageData['message_format']) {
            case 'text':
                $messageData['message_html'] = '';
                $event->setData($messageData);
                break;

            case 'html':
                // Let's assign a plaintext version to the text var
                // so the length constraints can still be tested.
                $messageData['message_text'] = Strings::stripTags($messageData['message_html']);
                $event->setData($messageData);
                break;
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();

        /** @var \Application\DeskPRO\Entity\TicketMessage $message */
        $message = $event->getData();

        // Manually bind the data using the appropriate setter
        switch ($form->get('message_format')->getData()) {
            case 'text':
                $message->setMessageText($form->get('message_text')->getData());
                break;

            case 'html':
                $message->setMessageHtml($form->get('message_html')->getData());
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ticket_description';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'          => 'Application\\DeskPRO\\Entity\\TicketMessage',
                'message_label'       => $this->language_manager->phrase('portal.forms.label_message'),
                'message_constraints' => [],
                'attr'                => ['data-rte' => '1'],
            ])
            ->setRequired([
                'person',
                'ticket',
            ])
            ->setAllowedTypes([
                'person' => 'Application\\DeskPRO\\Entity\\Person',
                'ticket' => 'Application\\DeskPRO\\Entity\\Ticket',
            ])
        ;
    }
}
