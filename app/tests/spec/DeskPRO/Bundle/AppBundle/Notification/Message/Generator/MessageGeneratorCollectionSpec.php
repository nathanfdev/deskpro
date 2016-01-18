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
