<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpMethod;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpClass
 */
class PhpClassSpec extends ObjectBehavior
{
    public function it_has_a_mutable_name()
    {
        $this->getName()->shouldBe(null);

        $this->setName('FilterTickets');

        $this->getName()->shouldBe('FilterTickets');
    }

    public function it_has_a_collection_of_properties()
    {
        $this->getProperties()->shouldBe([]);

        $this->addProperty('ticket');
        $this->addProperty('em', 'protected', 'null');

        $this->getProperties()->shouldBe(
            [
                'ticket' => [
                    'name'       => 'ticket',
                    'visibility' => 'public',
                    'default'    => null,
                ],
                'em' => [
                    'name'       => 'em',
                    'visibility' => 'protected',
                    'default'    => 'null',
                ],
            ]
        );

        $this->removeProperty('ticket');

        $this->getProperties()->shouldBe(
            [
                'em' => [
                    'name'       => 'em',
                    'visibility' => 'protected',
                    'default'    => 'null',
                ],
            ]
        );

        $this->getProperty('em')->shouldBe(
            [
                'name'       => 'em',
                'visibility' => 'protected',
                'default'    => 'null',
            ]
        );
    }

    public function it_has_a_collection_of_methods(
        \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpMethod $method1,
        \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpMethod $method2
    ) {
        $method1->getName()->willReturn('m1');
        $method2->getName()->willReturn('m2');

        $this->getMethods()->shouldBe([]);
        $this->addMethod($method1);
        $this->addMethod($method2);

        $this->getMethods()->shouldBe(['m1' => $method1, 'm2' => $method2]);

        $this->getMethod('m1')->shouldBe($method1);
        $this->getMethod('m2')->shouldBe($method2);
    }

    public function it_will_generate_a_random_name_and_assign_it_to_a_method_if_added_with_no_name_and_return_it(
        \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpMethod $method1
    ) {
        $method1->getName()->willReturn(null);
        $method1->setName(Argument::type('string'))->shouldBeCalled();

        $returned_method_name = $this->addMethod($method1);

        expect(strlen($returned_method_name->getWrappedObject()) > 5)->toBe(true);
    }

    public function it_throws_if_a_method_with_that_name_already_exists(
        PhpMethod $method1,
        \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpMethod $method2
    ) {
        $method1->getName()->willReturn('m1');
        $method2->getName()->willReturn('m1');

        $this->getMethods()->shouldBe([]);
        $this->addMethod($method1);

        $this->shouldThrow('\InvalidArgumentException')->during(
            'addMethod',
            [$method2]
        );

        $this->getMethods()->shouldBe(['m1' => $method1]);
    }

    public function it_can_implement_or_extend()
    {
        $this->getImplements()->shouldBe([]);
        $this->getExtends()->shouldBe(null);

        $this->addImplement('\StdClass');
        $this->addImplement('\IteratorAggregate');
        $this->setExtends('\DeskPRO\Bundle\AppBundle\SomeClass');

        $this->getImplements()->shouldBe(['\StdClass', '\IteratorAggregate']);
        $this->getExtends()->shouldBe('\DeskPRO\Bundle\AppBundle\SomeClass');
    }

    public function it_creates_a_string_of_itself(
        PhpMethod $method1,
        \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpMethod $method2
    ) {
        $method1->getName()->willReturn('isTicketCheck');
        $method1->__toString()->willReturn(
            'public function isTicketCheck(Ticket $ticket, PhpContext $context)
{
    do_something($ticket, $context);
}'
        );

        $method2->getName()->willReturn('verify');
        $method2->__toString()->willReturn(
            'protected function verify(Ticket $ticket)
{
    return do_something_else($ticket);
}'
        );

        $this->addMethod($method1);
        $this->addMethod($method2);

        $this->setName('xyz_class');
        $this->addImplement('\DeskPRO\Bundle\AppBundle\TermEngine\Something');
        $this->addImplement('\Countable');
        $this->setExtends('\ArrayObject');
        $this->addProperty('ticket', 'protected', 'null');
        $this->addProperty('enabled', null, 'false');
        $this->addProperty('simple', null, 'simple string');
        $this->addProperty('test');

        $this->__toString()->shouldBeLike(
            'class xyz_class extends \ArrayObject implements \DeskPRO\Bundle\AppBundle\TermEngine\Something, \Countable
{
protected $ticket = null;
public $enabled = false;
public $simple = \'simple string\';
public $test;

public function isTicketCheck(Ticket $ticket, PhpContext $context)
{
    do_something($ticket, $context);
}

protected function verify(Ticket $ticket)
{
    return do_something_else($ticket);
}
}'
        );
    }

    public function its_string_works_in_simplest_case()
    {
        $this->setName('MyClass444444444444444');
        $this->__toString()->shouldBe(
            'class MyClass444444444444444
{



}'
        );
    }

    public function its_string_works_with_just_an_extends()
    {
        $this->setName('MyClass444444444444444');
        $this->setExtends('\ArrayAccess');
        $this->__toString()->shouldBe(
            'class MyClass444444444444444 extends \ArrayAccess
{



}'
        );
    }

    public function its_string_works_with_just_an_implement()
    {
        $this->setName('MyClass444444444444444');
        $this->addImplement('\Countable');
        $this->__toString()->shouldBe(
            'class MyClass444444444444444 implements \Countable
{



}'
        );
    }

    public function it_allows_helper_methods_for_generating_constructor()
    {
        $this->addDependencyInjection('context', '\DeskPRO\Bundle\AppBundle\TermEngine\TermEngineContext');

        $this->getProperties()->shouldBe(
            [
                'context' => [
                    'name'       => 'context',
                    'visibility' => 'protected',
                    'default'    => null,
                ],
            ]
        );

        $method = $this->getMethod('__construct');

        $method->getName()->shouldBe('__construct');
        $method->getArguments()->shouldBe(
            [
                'context' => [
                    'name'    => 'context',
                    'type'    => '\DeskPRO\Bundle\AppBundle\TermEngine\TermEngineContext',
                    'default' => null,
                ],
            ]
        );

        $this->setName('my_class');

        $this->__toString()->shouldBeLike(
            'class my_class
{
protected $context;

public function __construct(\DeskPRO\Bundle\AppBundle\TermEngine\TermEngineContext $context)
{
    $this->context = $context;
}
}'
        );
    }
}
