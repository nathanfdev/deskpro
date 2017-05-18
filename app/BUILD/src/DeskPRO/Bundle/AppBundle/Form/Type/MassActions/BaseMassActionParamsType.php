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

namespace DeskPRO\Bundle\AppBundle\Form\Type\MassActions;

use Application\DeskPRO\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BaseMassActionParamsType.
 */
class BaseMassActionParamsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (count($options['actions'])) {
            $builder->add('set_of_actions', ChoiceType::class, [
                'choices_as_values' => true,
                'multiple'          => true,
                'choices'           => $options['actions'],
                'mapped'            => false,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'error_bubbling' => true,
            ])
            ->setRequired(['actions', 'person'])
            ->setAllowedTypes('actions', 'array')
            ->setAllowedTypes('person', Person::class)
        ;
    }

    /**
     * @param FormEvent $event
     * @param string    $action
     *
     * @return bool
     */
    public static function hasAction(FormEvent $event, $action)
    {
        $form    = $event->getForm();
        $actions = $form->get('set_of_actions')->getData();

        return is_array($actions) && in_array($action, $actions);
    }
}
