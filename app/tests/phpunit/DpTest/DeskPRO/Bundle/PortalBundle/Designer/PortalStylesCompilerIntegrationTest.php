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

/**
 * DeskPRO.
 */
namespace DpTest\Bundle\AppBundle\DataService\Content;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\PortalBundle\Brand\BrandContainer;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Designer\PortalStylesCompiler;
use DpTest\PortalTestCase;

/**
 * Class PortalStylesCompilerIntegrationTest.
 */
class PortalStylesCompilerIntegrationTest extends PortalTestCase
{
    /**
     * @var PortalStylesCompiler
     */
    private $service;

    /**
     * @var Brand
     */
    private $brand;

    /**
     * @var BrandStack
     */
    private $brand_stack;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->brand = new Brand();
        $this->brand->setName('Test Brand');
        $this->brand->setThemeSet($theme_set = new ThemeSet());
        $this->brand->setEditThemeSet($edit_theme_set = new ThemeSet());
        $theme_set->setThemeId('theme_set_id');
        $edit_theme_set->setThemeId('theme_set_id');

        $em = $this->getEntityManager();
        $em->persist($this->brand);
        $em->persist($theme_set);
        $em->persist($edit_theme_set);
        $em->flush();

        /** @var BrandContainer $brand_container */
        $brand_container = $this->getMockBuilder(BrandContainer::class)->disableOriginalConstructor()->getMock();
        $brand_container->method('getBrand')->willReturn($this->brand);

        /** @var BrandStack $brand_stack */
        $brand_stack = $this->getMockBuilder(BrandStack::class)->disableOriginalConstructor()->getMock();
        $brand_stack->method('getActive')->willReturn($brand_container);
        $this->brand_stack = $brand_stack;

        $this->service = $this->createService('dummy-empty.scss');
    }

    /**
     * @param string $style Style file path
     *
     * @return PortalStylesCompiler
     */
    private function createService($style)
    {
        return new PortalStylesCompiler(
            $this->getEntityManager(), $this->brand_stack, __DIR__."/scss/$style", 'custom-vars.scss');
    }

    /**
     * @param string $first
     * @param string $second
     */
    private function assertEqualCss($first, $second)
    {
        $this->assertEquals(preg_replace('/\s+/', '', $first), preg_replace('/\s+/', '', $second));
    }

    /**
     * @test
     */
    public function it_should_be_a_service()
    {
        $this->assertInstanceOf(PortalStylesCompiler::class, $this->get('dp.portal.designer.portal_styles_compiler'));
    }

    /**
     * @test
     */
    public function it_should_return_null_if_no_CSS_file_stored_in_the_Edit_ThemeSet()
    {
        $this->assertNull($this->service->getEditThemeSetCssBlobStorage());
    }

    /**
     * @test
     */
    public function it_should_create_CSS_blob_on_the_edit_ThemeSet_asset()
    {
        $this->assertNull($this->service->getEditThemeSetCssBlobStorage());
        $this->service->recompile([]);
        $this->assertNotNull($this->service->getEditThemeSetCssBlobStorage());
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function it_should_throw_an_Exception_when_styles_file_does_not_exist()
    {
        $this->createService('does_not_exist.scss');
    }

    /**
     * @test
     */
    public function it_should_compile_SCSS_into_CSS()
    {
        $service = $this->createService('dummy-no-vars.scss');
        $service->recompile([]);
        $this->assertEqualCss(
            $service->getEditThemeSetCssBlobStorage()->getData(),
            '.dummy-container .dummy-style { background: #fffaaa; }'
        );
    }

    /**
     * @test
     */
    public function it_should_compile_color_variables()
    {
        $service = $this->createService('dummy-color.scss');
        $service->recompile(['color' => '#123456']);
        $this->assertEqualCss(
            $service->getEditThemeSetCssBlobStorage()->getData(),
            '.dummy-style { background: #123456; }'
        );
    }

    /**
     * @test
     */
    public function it_should_compile_compound_size_variables()
    {
        $service = $this->createService('dummy-size.scss');
        $service->recompile(['size' => ['value' => 42, 'unit' => '%']]);
        $this->assertEqualCss(
            $service->getEditThemeSetCssBlobStorage()->getData(),
            '.dummy-style { margin-top: 42%; }'
        );
    }

    /**
     * @test
     */
    public function it_should_compile_compound_font_variables()
    {
        $service = $this->createService('dummy-font.scss');
        $service->recompile(['font' => ['font' => 'Arial', 'size' => 42, 'unit' => '%']]);
        $this->assertEqualCss(
            $service->getEditThemeSetCssBlobStorage()->getData(),
            '.dummy-style { font: 42% Arial; }'
        );
    }

    /**
     * @test
     */
    public function it_should_compile_font_when_it_is_set_to_a_custom_value()
    {
        $service = $this->createService('dummy-font.scss');
        $service->recompile(['font' => 'custom string']);
        $this->assertEqualCss(
            $service->getEditThemeSetCssBlobStorage()->getData(),
            '.dummy-style { font: custom string; }'
        );
    }
}
