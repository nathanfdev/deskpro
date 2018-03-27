<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketLinks;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class TicketLinkType.
 */
class TicketLinkType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('parent', ApiBooleanType::class, [
            'description' => 'Set true if you want to make link ticket as parent for ticket',
            'mapped'      => false,
        ]);

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSetLink']);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return BaseTicketLinkType::class;
    }

    /**
     * @param FormEvent $event
     */
    public function onSetLink(FormEvent $event)
    {
        $form = $event->getForm();

        /** @var Ticket $ticket */
        $ticket = $form->getData();
        $ticket->disableAutoTicketProcess();

        /* @var Ticket $ticket */
        $linkTicket = $form->get('link_ticket')->getData();
        if ($linkTicket) {
            $linkTicket->disableAutoTicketProcess();

            if ($form->get('parent')->getData()) {
                $ticket->setParentTicket($linkTicket);
            } else {
                $ticket->addChildrenTicket($linkTicket);
            }
        }
    }
}
