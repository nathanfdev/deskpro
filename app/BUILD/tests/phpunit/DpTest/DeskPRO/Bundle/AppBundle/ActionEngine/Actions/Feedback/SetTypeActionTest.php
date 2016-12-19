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

namespace DpTest\DeskPRO\Bundle\AppBundle\ActionEngine\Actions\Feedback;

use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\AbstractAction;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\ActionInterface;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\ActionWithOptionsInterface;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\Feedback\SetTypeAction;
use DeskPRO\Bundle\AppBundle\ActionEngine\OptionsResolver\ActionOptionsResolver;
use DpTest\DeskProTestCase;
use Prophecy\Argument;

class SetTypeActionTest extends DeskProTestCase
{
    /**
     * @test
     */
    public function it_should_be_instantiable()
    {
        $action = new SetTypeAction(['set_type' => 1]);
        $this->assertInstanceOf(SetTypeAction::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertInstanceOf(ActionWithOptionsInterface::class, $action);
    }

    /**
     * @test
     */
    public function it_should_extend_AbstractAction()
    {
        $this->assertContains(AbstractAction::class, class_parents(SetTypeAction::class));
    }

    /**
     * @test
     */
    public function it_should_configure_the_id_param()
    {
        $resolver = $this->prophesize(ActionOptionsResolver::class);
        $resolver
            ->setRequired(Argument::exact('set_type'))
            ->shouldBeCalled();
        $resolver
            ->setAllowedTypes(Argument::exact('set_type'), Argument::exact(['string', 'int']))
            ->shouldBeCalled();
        $resolver
            ->setAllowedValues(
                Argument::exact('set_type'),
                Argument::that(
                    function ($value) {
                        return !empty($value);
                    }
                )
            )
            ->shouldBeCalled();
        $resolver = $resolver->reveal();
        SetTypeAction::configureOptions($resolver);
    }

    /**
     * @test
     * @expectedException \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function it_should_raise_exception_on_the_empty_id_param()
    {
        new SetTypeAction(['set_type' => 0]);
    }

    /**
     * @test
     * @expectedException \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function it_should_raise_exception_on_the_none_integer_id_param()
    {
        new SetTypeAction(['set_type' => ['one']]);
    }

    /**
     * @test
     * @expectedException \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     */
    public function it_should_raise_exception_on_the_none_id_param()
    {
        new SetTypeAction(['something' => []]);
    }
}
