<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Approval;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate;
use DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TicketApprovalType
 *
 * @package DeskPRO\Bundle\AppBundle\Form\Type\Approval
 */
class TicketApprovalType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * BaseApprovalType constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

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
        $builder->setEmptyData(function (FormInterface $form) use ($options) {
            /** @var ApprovalTemplate|null $template */
            if ($template = $form->get('template')->getData()) {
                return call_user_func(
                    [$options['data_class'], 'createTicketApprovalFromTemplate'],
                    $options['ticket'],
                    $this->em,
                    $template
                );
            }

            return null;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TicketApproval::class,
        ]);

        $resolver->setRequired(['ticket']);

        $resolver->setAllowedValues('ticket', function ($value) {
            return $value instanceof Ticket;
        });
    }
}
