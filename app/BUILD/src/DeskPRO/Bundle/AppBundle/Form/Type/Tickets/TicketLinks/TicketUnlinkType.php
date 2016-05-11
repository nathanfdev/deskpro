<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketLinks;

use Application\DeskPRO\Entity\Ticket;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TicketUnlinkType.
 */
class TicketUnlinkType extends AbstractType
{
    const LINK_TYPE_PARENT  = 'parent';
    const LINK_TYPE_CHILD   = 'child';
    const LINK_TYPE_SIBLING = 'sibling';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('link_type', ChoiceType::class, [
            'mapped'            => false,
            'choices_as_values' => true,
            'values'            => [
                self::LINK_TYPE_PARENT,
                self::LINK_TYPE_CHILD,
                self::LINK_TYPE_SIBLING,
            ],
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onUnsetLink']);
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
    public function onUnsetLink(FormEvent $event)
    {
        $form = $event->getForm();

        /** @var Ticket $ticket */
        $ticket = $form->getData();
        $ticket->disableAutoTicketProcess();

        /* @var Ticket $ticket */
        $linkTicket = $form->get('link_ticket')->getData();
        $linkTicket->disableAutoTicketProcess();

        switch ($form->get('link_type')->getData()) {
            case self::LINK_TYPE_PARENT:
                $ticket->setParentTicket(null);
                break;
            case self::LINK_TYPE_CHILD:
                $ticket->removeChildrenTicket($linkTicket);
                break;
            case self::LINK_TYPE_SIBLING:
                $parent = $ticket->getParentTicket();
                if ($parent) {
                    $parent->removeChildrenTicket($linkTicket);
                }

                break;
        }
    }
}
