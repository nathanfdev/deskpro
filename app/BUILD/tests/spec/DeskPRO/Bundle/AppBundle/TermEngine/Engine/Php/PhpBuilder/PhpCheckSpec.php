<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder;

use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck
 */
class PhpCheckSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('my.expression');
    }

    public function it_must_be_constructed_with_its_expression()
    {
        $this->getExpression()->shouldBe('my.expression');

        $this->setExpression('other_expression(here)');

        $this->getExpression()->shouldBe('other_expression(here)');
    }

    public function it_always_trims_the_expression()
    {
        $this->beConstructedWith(
            '



        some.expression                                     '
        );
        $this->__toString()->shouldBe('some.expression');
    }

    public function it_holds_variables()
    {
        $this->getVariables()->shouldBe([]);

        $this->setVariable('test', [1, 2, 3]);
        $this->setVariable('other', 'bar');

        $this->getVariables()->shouldBe(
            [
                'test'  => [1, 2, 3],
                'other' => 'bar',
            ]
        );
    }

    public function it_renames_params()
    {
        $this->beConstructedWith('testing == :other and other == :test');
        $this->setVariable('test', [1, 2, 3]);
        $this->setVariable('other', 'bar');

        $this->renameVariable('test', 'name');
        $this->renameVariable('other', 'other1');

        $this->getExpression()->shouldBe(
            'testing == :other1 and other == :name'
        );
    }

    public function it_lets_you_freeze_variable_names_meaning_it_removes_colon_from_expression_string()
    {
        $this->beConstructedWith('testing == :other and other1 == :test');
        $this->setVariable('test', [1, 2, 3]);
        $this->setVariable('other', 'bar');

        $this->getExpression()->shouldBe(
            'testing == :other and other1 == :test'
        );

        $this->freezeVariableNames();

        $this->getExpression()->shouldBe(
            'testing == other and other1 == test'
        );
    }
}
