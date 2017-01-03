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
