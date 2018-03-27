<?php

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
            [
                [
                    'event'  => 'serializer.post_serialize',
                    'method' => 'onPostSerialize',
                ],
            ]
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
