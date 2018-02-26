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

namespace Application\DeskPRO\CustomFields\Form;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AliasType extends AbstractType implements EventSubscriberInterface
{
    private $nullHandlingStrategy = 'string';

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->nullHandlingStrategy = $options['null_handling_strategy'];
        $builder->addEventSubscriber($this);
    }

    public function configureOptions( OptionsResolver $resolver )
    {
        $resolver->setDefaults(array(
            'compound' => false,
            'null_handling_strategy' => 'string'
        ));
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $alias = $event->getData();
        if ($this->nullHandlingStrategy === 'null' && is_null($alias)) {
            return;
        }

        if (is_array($alias)) {
            return;
        }

        $value = $alias ? (string) $alias : '';
        $event->setData(new StringObject($value));
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT  => 'onPreSubmit'
        ];
    }
}
