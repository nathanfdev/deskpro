<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Application\DeskPRO\Entity\CustomDataBilling;
use Application\DeskPRO\Entity\CustomDefBilling;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketCharge;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TicketChargeType.
 */
class TicketChargeType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('charge_time', IntegerType::class)
            ->add('amount', NumberType::class)
            ->add('comment', TextareaType::class, [
                'mapped' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => TicketCharge::class,
            ])
            ->setRequired([
                'ticket',
                'person',
            ])
            ->setAllowedTypes('person', Person::class)
            ->setAllowedTypes('ticket', Ticket::class)
        ;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form    = $event->getForm();
        $data    = $event->getData();
        $options = $form->getConfig()->getOptions();

        if ($data instanceof TicketCharge) {
            // set relation objects
            /** @var Ticket $ticket */
            $ticket = $options['ticket'];

            $data->setAgent($options['person']);
            $data->setTicket($ticket);
            $data->setPerson($ticket->getPerson());
            $data->setOrganization($ticket->getOrganization());

            $ticket->getCharges()->add($data);

            // set comment to a custom field
            $comment = $form->get('comment')->getData();
            if ($comment) {
                $customDef = $this->em->getRepository(CustomDefBilling::class)->findOneBy(['title' => 'Comment']);
                if ($customDef) {
                    $customData = $data
                        ->getCustomData()
                        ->filter(function (CustomDataBilling $customData) use ($customDef) {
                            return $customData->getRootField() === $customDef;
                        })
                        ->first()
                    ;

                    if (!$customData) {
                        $customData = new CustomDataBilling();
                        $customData->setField($customDef);
                        $customData->setRootField($customDef);
                        $customData->setValue(0);
                    }

                    $customData->setInput($comment);

                    $data->getCustomData()->add($customData);
                    $customData->setTicketCharge($data);
                }
            }
        }
    }
}
