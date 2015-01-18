<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\FormBundle\Form\Type;

use Application\DeskPRO\Entity\TicketMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;

class TicketMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (count($options['message_constraints'])) {
            $constraints = $options['message_constraints'];
        } else {
            $constraints = array(
                new NotNull(),
                new Length(array('min' => 10, 'minMessage' => 'Your message must be at least 10 characters in length.'))
            );
        }

        $builder->add('message', 'textarea', array(
            'label' => $options['message_label'],
            'required' => $options['required'],
            'constraints' => $constraints
        ));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreData'));
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

    public function getName()
    {
        return 'ticket_message';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Application\\DeskPRO\\Entity\\TicketMessage',
            'message_label' => 'Message',
            'message_constraints' => array()
        ));
        $resolver->setRequired(array(
            'person', 'ticket'
        ));
        $resolver->setAllowedTypes(array(
            'person' => 'Application\\DeskPRO\\Entity\\Person',
            'ticket' => 'Application\\DeskPRO\\Entity\\Ticket'
        ));
    }
}
