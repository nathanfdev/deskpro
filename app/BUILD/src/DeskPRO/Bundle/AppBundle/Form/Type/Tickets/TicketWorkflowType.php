<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Application\DeskPRO\Entity\TicketWorkflow;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class WorkflowType.
 */
class TicketWorkflowType extends AbstractType
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
            'class'         => TicketWorkflow::class,
            'choice_label'  => 'title',
            'empty_data'    => null,
            'required'      => true,
            'query_builder' => function (EntityRepository $repository) {
                return $repository
                    ->createQueryBuilder('w')
                    ->select('w')
                    ->addOrderBy('w.display_order')
                ;
            },
        ]);
    }
}
