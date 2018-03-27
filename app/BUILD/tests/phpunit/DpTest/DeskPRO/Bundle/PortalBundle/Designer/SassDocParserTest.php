<?php

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
