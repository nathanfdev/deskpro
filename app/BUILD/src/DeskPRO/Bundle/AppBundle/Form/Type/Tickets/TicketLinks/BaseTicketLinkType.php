<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketLinks;

use Application\DeskPRO\Entity\Ticket;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class BaseTicketLinkType.
 */
class BaseTicketLinkType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('link_ticket', EntityType::class, [
                'class'       => Ticket::class,
                'mapped'      => false,
                'constraints' => [
                    new Assert\NotNull(),
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'error_mapping' => [
                'parent_ticket'    => 'link_ticket',
                'children_tickets' => 'link_ticket',
            ],
        ]);
    }
}
