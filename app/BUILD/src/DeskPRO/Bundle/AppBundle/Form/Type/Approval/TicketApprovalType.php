<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Approval;

use DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TicketApproval::class,
        ]);
    }
}
