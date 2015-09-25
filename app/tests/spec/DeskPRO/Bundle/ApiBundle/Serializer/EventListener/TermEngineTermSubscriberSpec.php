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
namespace spec\DeskPRO\Bundle\ApiBundle\Serializer\EventListener;

use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\JsonSerializationVisitor;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin \DeskPRO\Bundle\ApiBundle\Serializer\EventListener\TermEngineTermSubscriber
 */
class TermEngineTermSubscriberSpec extends ObjectBehavior
{
    public function it_listens_to_post_serialize()
    {
        $this->shouldHaveType('JMS\Serializer\EventDispatcher\EventSubscriberInterface');
        $this->getSubscribedEvents()->shouldBe(
            array(
                array(
                    'event'  => 'serializer.post_serialize',
                    'method' => 'onPostSerialize',
                ),
            )
        );
    }

    public function it_ignores_non_term_interface_objects(
        ObjectEvent $event,
        JsonSerializationVisitor $visitor
    ) {
        // assume the object in question is a DateTime, we aren't interested...
        $event->getObject()->willReturn(new \DateTime());
        $event->getVisitor()->willReturn($visitor);

        // we should not be adding data to this visitor...
        // if we add to the visitor with a DateTime, this will fail
        $visitor->addData('type', Argument::any())->shouldNotBeCalled();

        $this->onPostSerialize($event);
    }

    public function it_adds_type_to_each_term_interface_using_the_converter_logic(
        ObjectEvent $event,
        JsonSerializationVisitor $visitor,
        TermInterface $term
    ) {
        // this time it is a legit term interface object
        $event->getObject()->willReturn($term);
        $event->getVisitor()->willReturn($visitor);

        // now we SHOULD call this...
        $visitor->addData('type', Argument::any())->shouldBeCalled();

        // run it
        $this->onPostSerialize($event);
    }
}
