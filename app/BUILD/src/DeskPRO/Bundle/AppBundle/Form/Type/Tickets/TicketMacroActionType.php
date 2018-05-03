<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use DeskPRO\Bundle\AppBundle\Form\Type\JsonArrayType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class TicketMacroActionType.
 */
class TicketMacroActionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', TextType::class)
            ->add('options', JsonArrayType::class)
        ;
    }
}
