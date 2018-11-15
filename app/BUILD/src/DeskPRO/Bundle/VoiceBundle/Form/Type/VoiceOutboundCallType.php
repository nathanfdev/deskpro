<?php

namespace DeskPRO\Bundle\VoiceBundle\Form\Type;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use DeskPRO\Bundle\AppBundle\Entity\VoiceNumber;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class VoiceOutboundCallType.
 */
class VoiceOutboundCallType extends AbstractType
{
    /**
     * @var PersonRepository
     */
    private $personRepo;

    /**
     * Constructor.
     *
     * @param PersonRepository $personRepo
     */
    public function __construct(PersonRepository $personRepo)
    {
        $this->personRepo = $personRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('call_from', EntityType::class, [
                'property_path' => 'number',
                'class'         => VoiceNumber::class,
                'required'      => true,
            ])
            ->add('call_to', TextType::class, [
                'property_path' => 'externalNumber',
                'required'      => true,
            ])
            ->add('ticket', EntityType::class, [
                'mapped'   => false,
                'required' => false,
                'class'    => Ticket::class,
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VoicePhoneCall::class,
        ]);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        if (!$data instanceof VoicePhoneCall) {
            return;
        }

        $options = [];

        // set related ticket
        $ticket = $form->get('ticket')->getData();
        if ($ticket instanceof Ticket) {
            $options['outgoing_ticket_id'] = $ticket->getId();
        }

        $data->setType(VoicePhoneCall::DIRECTION_OUTBOUND);
        $data->setData($options);

        if ($data->getExternalNumber()) {
            $person = $this->personRepo->getOrCreateUserByPhoneNumber($data->getExternalNumber());
            $data->setPerson($person);
        }
    }
}
