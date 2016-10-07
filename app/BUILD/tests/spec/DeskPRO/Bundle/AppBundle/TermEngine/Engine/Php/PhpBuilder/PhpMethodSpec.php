<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder;

use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpMethod
 */
class PhpMethodSpec extends ObjectBehavior
{
    public function it_has_a_mutable_name()
    {
        $this->getName()->shouldBe(null);
        $this->setName('name');
        $this->getName()->shouldBe('name');
    }

    public function it_has_a_mutable_method_body()
    {
        $this->getCode()->shouldBe(null);
        $this->setCode('<?php echo "hi";');
        $this->getCode()->shouldBe('<?php echo "hi";');
    }

    public function it_has_a_collection_or_arguemnts()
    {
        $this->getArguments()->shouldBe([]);

        $this->addArgument('ticket', 'Application\DeskPRO\Entity\Ticket');
        $this->addArgument('string', null, 'def');
        $this->addArgument('array', 'array', 'array()');

        $this->getArguments()->shouldBe(
            [
                'ticket' => [
                    'name'    => 'ticket',
                    'type'    => 'Application\DeskPRO\Entity\Ticket',
                    'default' => null,
                ],
                'string' => [
                    'name'    => 'string',
                    'type'    => null,
                    'default' => 'def',
                ],
                'array' => [
                    'name'    => 'array',
                    'type'    => 'array',
                    'default' => 'array()',
                ],
            ]
        );

        $this->removeArgument('ticket');
        $this->removeArgument('string');

        $this->getArguments()->shouldBe(
            [
                'array' => [
                    'name'    => 'array',
                    'type'    => 'array',
                    'default' => 'array()',
                ],
            ]
        );
    }

    public function it_has_a_visibility()
    {
        $this->getVisibility()->shouldBe('public');

        $this->setVisibility('private');

        $this->getVisibility()->shouldBe('private');
    }

    public function it_can_convert_itself_into_a_string()
    {
        $this->addArgument('ticket', '\Application\DeskPRO\Entity\Ticket');
        $this->addArgument('string', null, 'def');
        $this->addArgument('array', 'array', 'array()');
        $this->addArgument('other');

        $this->setCode('return $ticket->getId() > 5;');

        $this->setName('myFunc');

        $this->setVisibility('protected');

        $this->__toString()->shouldBeLike(
            'protected function myFunc(\Application\DeskPRO\Entity\Ticket $ticket, $string = \'def\', array $array = array(), $other)
{
    return $ticket->getId() > 5;
}'
        );
    }
}
