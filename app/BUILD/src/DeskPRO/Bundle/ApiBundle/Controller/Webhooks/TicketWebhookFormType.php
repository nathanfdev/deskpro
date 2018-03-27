<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Webhooks;

use Application\DeskPRO\Entity\TicketTrigger;
use DeskPRO\Bundle\AppBundle\Entity\Webhooks\TicketWebhook;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TicketWebhookFormType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'mapped'   => true,
            ])
            ->add('payload_decoder', TextType::class, [
                'label'    => '',
                'required' => false,
                'mapped'   => true,
                'empty_data' => ''
            ])
            ->add('is_enabled', CheckboxType::class, [
                'label'    => '',
                'required' => true,
                'mapped'   => true,
            ])
            ->add('search_terms', FilterTermsFormType::class, [
                'label'    => '',
                'required' => true,
                'mapped'   => true,
            ])
        ;

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['allow_extra_fields' => true]);
    }

}
