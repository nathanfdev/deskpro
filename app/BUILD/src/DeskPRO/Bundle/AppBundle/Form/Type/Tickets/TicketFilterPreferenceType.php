<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
