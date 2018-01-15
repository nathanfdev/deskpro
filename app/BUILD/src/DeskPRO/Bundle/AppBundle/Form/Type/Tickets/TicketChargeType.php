<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
