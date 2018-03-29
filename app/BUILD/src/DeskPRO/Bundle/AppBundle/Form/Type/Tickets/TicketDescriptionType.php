<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TicketDescriptionType.
 */
class TicketDescriptionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TicketMessageType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'render_is_note'      => false,
            'message_constraints' => [
                new Assert\NotBlank(), // use to avoid entity empty message string
                new Assert\Length(['min' => 10]),
            ],
        ]);
    }
}
