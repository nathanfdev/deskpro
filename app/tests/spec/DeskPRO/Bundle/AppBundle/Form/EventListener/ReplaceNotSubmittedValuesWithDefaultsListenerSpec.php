<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace spec\DeskPRO\Bundle\AppBundle\Form\EventListener;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\Form\EventListener\ReplaceNotSubmittedValuesWithDefaultsListener
 */
class ReplaceNotSubmittedValuesWithDefaultsListenerSpec extends ObjectBehavior
{
    public function it_is_a_form_event_subscriber()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\EventSubscriberInterface');
        $this->getSubscribedEvents()->shouldBe(
            array(
                FormEvents::PRE_SUBMIT => 'onPreSubmit',
            )
        );
    }

    public function it_replaces_not_submitted_form_data_with_defaults_if_not_a_required_field(
        FormEvent $event,
        FormInterface $form,
        FormConfigInterface $config,
        FormInterface $child_form1,
        FormInterface $child_form2
    ) {
        $event->getData()->willReturn(array()); // nothing submitted
        $event->getForm()->willReturn($form);
        $form->getConfig()->willReturn($config);
        $config->getCompound()->willReturn(true);
        $form->all()->willReturn(array('child1' => $child_form1, 'child2' => $child_form2));
        $child_form1->isRequired()->willReturn(true);
        $child_form2->isRequired()->willReturn(false);
        $child_form1->getData()->willReturn('data 1');
        $child_form2->getData()->willReturn('data 2');

        $event->setData(
            array(
                'child2' => 'data 2',
            )
        )->shouldBeCalled();

        $this->onPreSubmit($event);
    }
}
