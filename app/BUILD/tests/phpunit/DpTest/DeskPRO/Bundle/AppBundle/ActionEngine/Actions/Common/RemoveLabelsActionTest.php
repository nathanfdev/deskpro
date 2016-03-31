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

namespace DpTest\DeskPRO\Bundle\AppBundle\ActionEngine\Actions\Common;

use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\AbstractAction;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\ActionInterface;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\ActionWithOptionsInterface;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\Common\RemoveLabelsAction;
use DeskPRO\Bundle\AppBundle\ActionEngine\OptionsResolver\ActionOptionsResolver;
use DpTest\DeskProTestCase;
use Prophecy\Argument;

class RemoveLabelsActionTest extends DeskProTestCase
{
    /**
     * @test
     */
    public function it_should_be_instantiable()
    {
        $action = new RemoveLabelsAction(['options' => ['one', 'two']]);
        $this->assertInstanceOf(RemoveLabelsAction::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertInstanceOf(ActionWithOptionsInterface::class, $action);
    }

    /**
     * @test
     */
    public function it_should_extend_AbstractAction()
    {
        $this->assertContains(AbstractAction::class, class_parents(RemoveLabelsAction::class));
    }

    /**
     * @test
     */
    public function it_should_configure_the_labels_param()
    {
        $resolver = $this->prophesize(ActionOptionsResolver::class);
        $resolver->setRequired(Argument::exact('options'))->shouldBeCalled();
        $resolver->setAllowedTypes(Argument::exact('options'), Argument::exact('array'))->shouldBeCalled();
        $resolver->setAllowedValues(
            Argument::exact('options'),
            Argument::that(
                function ($value) {
                    return !empty($value);
                }
            )
        )->shouldBeCalled();
        $resolver = $resolver->reveal();
        RemoveLabelsAction::configureOptions($resolver);
    }

    /**
     * @test
     * @expectedException \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function it_should_raise_exception_on_the_empty_labels_param()
    {
        new RemoveLabelsAction(['options' => []]);
    }

    /**
     * @test
     * @expectedException \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function it_should_raise_exception_on_the_string_labels_param()
    {
        new RemoveLabelsAction(['options' => 'one']);
    }

    /**
     * @test
     * @expectedException \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     */
    public function it_should_raise_exception_on_the_none_labels_param()
    {
        new RemoveLabelsAction(['something' => []]);
    }
}
