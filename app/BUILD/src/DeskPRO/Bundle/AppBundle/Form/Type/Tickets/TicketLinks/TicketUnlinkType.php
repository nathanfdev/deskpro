<?php

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
            'choices'           => [
                self::LINK_TYPE_PARENT,
                self::LINK_TYPE_CHILD,
                self::LINK_TYPE_SIBLING,
            ],
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onRemoveLinkTicketForParent']);
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
    public function onRemoveLinkTicketForParent(FormEvent $event)
    {
        $data = $event->getData();

        if (isset($data['link_type']) && $data['link_type'] === self::LINK_TYPE_PARENT) {
            $event->getForm()->remove('link_ticket');
        }
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

        switch ($form->get('link_type')->getData()) {
            case self::LINK_TYPE_PARENT:
                $ticket->setParentTicket(null);
                break;
            case self::LINK_TYPE_CHILD:
                $linkedTicket = $this->getLinkTicket($event);
                if ($linkedTicket) {
                    $ticket->removeChildrenTicket($linkedTicket);
                }

                break;
            case self::LINK_TYPE_SIBLING:
                $parentTicket = $ticket->getParentTicket();
                $linkedTicket = $this->getLinkTicket($event);

                if ($parentTicket && $linkedTicket) {
                    $parentTicket->disableAutoTicketProcess();
                    $parentTicket->removeChildrenTicket($this->getLinkTicket($event));
                }

                break;
        }
    }

    /**
     * @param FormEvent $event
     *
     * @return Ticket
     */
    protected function getLinkTicket(FormEvent $event)
    {
        /* @var Ticket $ticket */
        $ticket = $event->getForm()->get('link_ticket')->getData();
        if ($ticket) {
            $ticket->disableAutoTicketProcess();
        }

        return $ticket;
    }
}
