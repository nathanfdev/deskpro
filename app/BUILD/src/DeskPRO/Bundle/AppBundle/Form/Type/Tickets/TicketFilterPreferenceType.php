<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilterPreference;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TicketFilterPreferenceType.
 */
class TicketFilterPreferenceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('main_grouping', TextType::class, [
                'required' => false,
            ])
            ->add('result_grouping', TextType::class, [
                'required' => false,
            ])
            ->add('display_order', IntegerType::class, [
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $pref = $event->getData();
            $config = $event->getForm()->getConfig();
            $pref->setAgent($config->getOption('agent'));
            $event->setData($pref);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'      => TicketFilterPreference::class,
                'main_grouping'   => '',
                'result_grouping' => '',
            ])
            ->setRequired(['filter', 'agent'])
            ->setAllowedTypes('filter', TicketFilter::class)
            ->setAllowedTypes('agent', Person::class)
        ;
    }
}
