<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Form\Type\EntityHierarchyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TicketDepartmentChoiceType.
 */
class TicketDepartmentChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EntityHierarchyType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'ticket'        => null, // provide a ticket so the ticket's dep is always in the hierarchy list
                'brand'         => null,
                'choice_loader' => function (Options $options) {
                    /** @var \DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyGenerator $generator */
                    $generator = $options['hierarchy_generator'];

                    return $generator
                        ->generateTicketDepartmentsHierarchy($options['person'], $options['ticket'], $options['brand'])
                        ->getChoiceLoader();
                },
            ])
            ->setRequired('person')
            ->setAllowedTypes('ticket', ['null', Ticket::class])
            ->setAllowedTypes('brand', ['null', Brand::class])
        ;
    }
}
