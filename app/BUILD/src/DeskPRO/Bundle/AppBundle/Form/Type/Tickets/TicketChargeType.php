<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Application\DeskPRO\Entity\CustomDataBilling;
use Application\DeskPRO\Entity\CustomDefBilling;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketCharge;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\Type\CombinedType;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomDataType;
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
     * @var CustomFieldManager
     */
    private $fieldManager;

    /**
     * Constructor.
     *
     * @param EntityManager      $em
     * @param CustomFieldManager $fieldManager
     */
    public function __construct(EntityManager $em, CustomFieldManager $fieldManager)
    {
        $this->em           = $em;
        $this->fieldManager = $fieldManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, [$this, 'onPostSetData']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'      => TicketCharge::class,
                'agent_interface' => false,
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
    public function onPostSetData(FormEvent $event)
    {
        $form    = $event->getForm();
        $options = $form->getConfig()->getOptions();

        $form
            ->add('charge_time', IntegerType::class)
            ->add('amount', NumberType::class)
            ->add('comment', TextareaType::class, [
                'mapped' => false,
            ])
            ->add('fields', CombinedType::class, [
                'forms'          => $this->getCustomDataFields($options),
                'error_bubbling' => false,
            ])
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

            $ticket->addChargeEntity($data);

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
                        $data->addCustomData($customData);
                    }

                    $customData->setInput($comment);
                    $customData->setTicketCharge($data);
                }
            }
        }
    }

    /**
     * @param array  $options
     *
     * @return array
     */
    private function getCustomDataFields(array $options)
    {
        $defs   = $this->fieldManager->getAvailableBillingDefs();
        $fields = [];

        foreach ($defs as $def) {
            $fields[] = [
                'name'    => $def->getId(),
                'type'    => CustomDataType::class,
                'options' => [
                    'custom_def'      => $def,
                    'property_path'   => 'custom_data',
                    'agent_interface' => $options['agent_interface'],
                    'inline'          => true,
                    'ticket'          => $options['ticket'],

                    // don't validate if custom field is required
                    // because we don't use ticket layouts here in this form
                    'check_required' => false,
                ],
            ];
        }

        return $fields;
    }
}
