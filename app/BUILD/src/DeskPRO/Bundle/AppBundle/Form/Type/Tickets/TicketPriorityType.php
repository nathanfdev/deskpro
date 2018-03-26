<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Application\DeskPRO\Entity\TicketPriority;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TicketPriorityType.
 */
class TicketPriorityType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EntityType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class'         => TicketPriority::class,
            'choice_label'  => 'title',
            'empty_data'    => null,
            'required'      => true,
            'query_builder' => function (EntityRepository $repo) {
                return $repo
                    ->createQueryBuilder('p')
                    ->select('p')
                    ->addOrderBy('p.priority')
                ;
            },
        ]);
    }
}
