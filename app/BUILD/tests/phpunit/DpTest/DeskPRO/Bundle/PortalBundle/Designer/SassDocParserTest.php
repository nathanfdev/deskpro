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

namespace DpTest\DeskPRO\Bundle\PortalBundle\Designer;

use DeskPRO\Bundle\PortalBundle\Designer\SassDocParser;
use DpTest\PortalTestCase;

/**
 * Class SassDocParserTest.
 */
class SassDocParserTest extends PortalTestCase
{
    /**
     * @var SassDocParser
     */
    private $service;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->service = new SassDocParser(__DIR__.'/sassdoc/vars.json');
    }

    /**
     * @test
     */
    public function it_should_be_instantiable()
    {
        $this->assertInstanceOf(SassDocParser::class, $this->service);
    }

    /**
     * @test
     */
    public function it_should_parse_size_values()
    {
        $values = $this->service->getVariableValues();
        $this->assertEquals(['value' => 1.5, 'unit' => '%'], $values['border-radius-main']);
    }

    /**
     * @test
     */
    public function it_should_parse_color_values()
    {
        $values = $this->service->getVariableValues();
        $this->assertEquals('purple', $values['button-color']);
    }

    /**
     * @test
     */
    public function it_should_parse_float_values()
    {
        $values = $this->service->getVariableValues();
        $this->assertEquals(0.5, $values['text-opacue']);
    }

    /**
     * @test
     */
    public function it_should_parse_font_values()
    {
        $values = $this->service->getVariableValues();
        $this->assertEquals('Georgia, serif', $values['main-font']);
    }
}
