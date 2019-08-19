<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Approval;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TicketApprovalType
 *
 * @package DeskPRO\Bundle\AppBundle\Form\Type\Approval
 */
class TicketApprovalType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return BaseApprovalType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('ticket', EntityType::class, [
            'required' => true,
            'class' => Ticket::class,
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TicketApproval::class,
        ]);
    }
}
