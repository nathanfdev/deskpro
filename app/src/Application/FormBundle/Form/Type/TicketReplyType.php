<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

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

class TicketReplyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('ticket_message', 'ticket_message', array(
            'ticket'        => $options['ticket'],
            'person'        => $options['person'],
            'message_label' => $options['message_label'],
            'label' => false,
            'message_constraints' => array(
                new Length(array('min' => 100))
            )
        ));

        $builder->add('attachments', 'ticket_message_attachment_collection', array(
            'ticket_message' => $options['ticket_message'],
            'person'         => $options['person'],
            'label' => false
        ));

        $builder->addEventListener(FormEvents::POST_SUBMIT, array($this, 'onPostSubmit'));
    }

    public function onPostSubmit(FormEvent $event)
    {
        /** @var \Application\DeskPRO\Entity\TicketMessage $message */
        /** @var \Application\DeskPRO\Entity\Ticket $ticket */
        $data = $event->getData();
        $message = $data['ticket_message'];
        $message->attachments = $data['attachments'];
    }

    public function getName()
    {
        return 'ticket_reply';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'message_label' => false
            )
        );
        $resolver->setRequired(array(
            'person', 'ticket', 'ticket_message'
        ));
        $resolver->setAllowedTypes(array(
            'ticket'         => 'Application\\DeskPRO\\Entity\\Ticket',
            'ticket_message' => 'Application\\DeskPRO\\Entity\\TicketMessage',
            'person'         => 'Application\\DeskPRO\\Entity\\Person'
        ));
    }
}
