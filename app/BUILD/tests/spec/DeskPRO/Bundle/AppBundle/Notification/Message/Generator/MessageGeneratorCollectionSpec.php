<?php

namespace spec\DeskPRO\Bundle\AppBundle\Notification\Message\Generator;

use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\MessageGeneratorCollection;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\MessageGeneratorInterface;
use PhpSpec\ObjectBehavior;

/**
 * @mixin MessageGeneratorCollection
 */
class MessageGeneratorCollectionSpec extends ObjectBehavior
{
    public function let(
        MessageGeneratorInterface $generator
    ) {
    }

    public function it_can_attach_generators(MessageGeneratorInterface $generator)
    {
        $generator->getType()->willReturn('message_generator');
        $this->addGenerator($generator);
        $generator->getType()->willReturn('yet_another_message_generator');
        $this->addGenerator($generator);
        $this->count()->shouldBe(2);
    }

    public function it_will_skip_same_generator_attachment(MessageGeneratorInterface $generator)
    {
        $generator->getType()->willReturn('message_generator');
        $this->addGenerator($generator);
        $this->addGenerator($generator);
        $this->count()->shouldBe(1);
    }
}
