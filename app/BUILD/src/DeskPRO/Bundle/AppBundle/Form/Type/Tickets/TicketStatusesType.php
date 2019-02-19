<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TicketStatusesType.
 */
class TicketStatusesType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status_type', ChoiceType::class, [
                'required' => true,
                'choices'  => [
                    TicketStatus::STATUS_TYPE_AWAITING_AGENT,
                    TicketStatus::STATUS_TYPE_AWAITING_USER,
                    TicketStatus::STATUS_TYPE_PENDING,
                    TicketStatus::STATUS_TYPE_RESOLVED,
                ],
                'choices_as_values' => true,
                'mapped'            => false,
            ])
            ->add('sys_id', TextType::class, [
                'required' => false,
            ])
            ->add('title', TextType::class, [
                'required' => true,
            ])
            ->add('parent', EntityType::class, [
                'class'    => TicketStatus::class,
                'required' => false,
            ])
            ->add('display_order', IntegerType::class, [
                'empty_data' => '0',
                'required'   => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        // be able to find Parent by `statusCode`
        $data = $event->getData();
        if (
            is_array($data)
            && array_key_exists('parent', $data)
            && !empty($data['parent'])
            && !is_numeric($data['parent'])
        ) {
            $parts = explode('.', $data['parent']);
            if (count($parts) == 2 && $parts[1]) {
                $data['parent'] = $parts[1];
            }
        }

        $event->setData($data);

        // disable `statusType` field for already existing statuses
        $form  = $event->getForm();
        $model = $form->getData();
        if ($model && $model->getId()) {
            $this->disableStatusType($form);
        }
    }

    /**
     * @param FormInterface $form
     */
    protected function disableStatusType(FormInterface $form)
    {
        $form->add('status_type', ChoiceType::class, [
            'required'          => false,
            'choices'           => [],
            'choices_as_values' => true,
            'mapped'            => false,
            'disabled'          => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => TicketStatus::class,
                'empty_data' => function (FormInterface $form) {
                    return new TicketStatus($form->get('status_type')->getData());
                },
            ])
        ;
    }
}
