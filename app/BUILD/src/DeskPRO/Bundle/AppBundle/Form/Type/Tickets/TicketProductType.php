<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use DeskPRO\Bundle\AppBundle\Form\Type\EntityHierarchyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TicketProductType.
 */
class TicketProductType extends AbstractType
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
        $resolver->setDefaults([
            'choice_loader' => function (Options $options) {
                /** @var \DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyGenerator $hierarchy_generator */
                $hierarchy_generator = $options['hierarchy_generator'];

                return $hierarchy_generator->generateTicketProductsHierarchy()->getChoiceLoader();
            },
        ]);
    }
}
