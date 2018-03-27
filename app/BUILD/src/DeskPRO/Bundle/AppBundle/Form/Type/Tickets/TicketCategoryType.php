<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use DeskPRO\Bundle\AppBundle\Form\Type\EntityHierarchyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TicketCategoryType.
 */
class TicketCategoryType extends AbstractType
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
                /** @var \DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyGenerator $hierarchyGenerator */
                $hierarchyGenerator = $options['hierarchy_generator'];

                return $hierarchyGenerator->generateTicketCategoriesHierarchy()->getChoiceLoader();
            },
        ]);
    }
}
