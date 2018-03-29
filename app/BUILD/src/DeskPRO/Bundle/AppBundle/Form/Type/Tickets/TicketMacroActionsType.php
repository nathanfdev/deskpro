<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TicketMacroActionsType.
 */
class TicketMacroActionsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type'   => TicketMacroActionType::class,
            'allow_add'    => true,
            'allow_delete' => true,
        ]);
    }
}
