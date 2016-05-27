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

namespace DpTest\DeskPRO\Bundle\PortalBundle\Designer;

use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
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
     * @var ThemeSet
     */
    private $edit_theme_set;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->edit_theme_set = new ThemeSet();
        $this->edit_theme_set->setThemeId('edit_theme_set_id');
        $this->getEntityManager()->persist($this->edit_theme_set);
        $this->getEntityManager()->flush();

        $this->service = $this->createService('dummy-empty.scss');
    }

    /**
     * @param string $style       Style file path
     * @param string $custom_scss
     *
     * @return PortalStylesCompiler
     */
    private function createService($style, $custom_scss = '')
    {
        return new PortalStylesCompiler(
            $this->getEntityManager(),
            __DIR__."/scss/$style",
            __DIR__."/scss-rtl/$style",
            $custom_scss
        );
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
    public function it_should_return_null_if_no_CSS_file_stored_in_the_Edit_ThemeSet()
    {
        foreach (['LTR', 'RTL'] as $dir) {
            $this->assertNull($this->service->getCssBlobStorage($this->edit_theme_set, $dir));
        }
    }

    /**
     * @test
     */
    public function it_should_create_CSS_blob_on_the_edit_ThemeSet_asset()
    {
        foreach (['LTR', 'RTL'] as $dir) {
            $this->assertNull($this->service->getCssBlobStorage($this->edit_theme_set, $dir));
        }
        $this->service->recompile([], $this->edit_theme_set);
        foreach (['LTR', 'RTL'] as $dir) {
            $this->assertNotNull($this->service->getCssBlobStorage($this->edit_theme_set, $dir));
        }
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
        $service->recompile([], $this->edit_theme_set);
        $this->assertEqualCss(
            $service->getCssBlobStorage($this->edit_theme_set, 'LTR')->getData(),
            '.dummy-container .dummy-style { background: #fffaaa; }'
        );
        $this->assertEqualCss(
            $service->getCssBlobStorage($this->edit_theme_set, 'RTL')->getData(),
            '.dummy-container .dummy-style-rtl { background: #fffaaa; }'
        );
    }

    /**
     * @test
     */
    public function it_should_compile_color_variables()
    {
        $service = $this->createService('dummy-color.scss');
        $service->recompile(['color' => '#123456'], $this->edit_theme_set);
        $this->assertEqualCss(
            $service->getCssBlobStorage($this->edit_theme_set, 'LTR')->getData(),
            '.dummy-style { background: #123456; }'
        );
        $this->assertEqualCss(
            $service->getCssBlobStorage($this->edit_theme_set, 'RTL')->getData(),
            '.dummy-style-rtl { background: #123456; }'
        );
    }

    /**
     * @test
     */
    public function it_should_compile_compound_size_variables()
    {
        $service = $this->createService('dummy-size.scss');
        $service->recompile(['size' => ['value' => 42, 'unit' => '%']], $this->edit_theme_set);
        $this->assertEqualCss(
            $service->getCssBlobStorage($this->edit_theme_set, 'LTR')->getData(),
            '.dummy-style { margin-top: 42%; }'
        );
        $this->assertEqualCss(
            $service->getCssBlobStorage($this->edit_theme_set, 'RTL')->getData(),
            '.dummy-style-rtl { margin-top: 42%; }'
        );
    }

    /**
     * @test
     */
    public function it_should_compile_font_variables()
    {
        $service = $this->createService('dummy-font.scss');
        $service->recompile(['font' => 'Arial Test'], $this->edit_theme_set);
        $this->assertEqualCss(
            $service->getCssBlobStorage($this->edit_theme_set, 'LTR')->getData(),
            '.dummy-style { font: Arial Test; }'
        );
        $this->assertEqualCss(
            $service->getCssBlobStorage($this->edit_theme_set, 'RTL')->getData(),
            '.dummy-style-rtl { font: Arial Test; }'
        );
    }

    /**
     * @test
     */
    public function it_should_compile_font_when_it_is_set_to_a_custom_value()
    {
        $service = $this->createService('dummy-font.scss');
        $service->recompile(['font' => 'custom string'], $this->edit_theme_set);
        $this->assertEqualCss(
            $service->getCssBlobStorage($this->edit_theme_set, 'LTR')->getData(),
            '.dummy-style { font: custom string; }'
        );
        $this->assertEqualCss(
            $service->getCssBlobStorage($this->edit_theme_set, 'RTL')->getData(),
            '.dummy-style-rtl { font: custom string; }'
        );
    }

    /**
     * @test
     */
    public function it_should_compile_custom_SCSS()
    {
        $service = $this->createService('dummy-custom-style.scss', '
            .dp-test-custom-scss {
                .dp-test-custom-inner {
                    color: purple;
                }
            }
        ');
        $service->recompile([], $this->edit_theme_set);
        $this->assertEqualCss(
            $service->getCssBlobStorage($this->edit_theme_set, 'LTR')->getData(),
            '.dp-test-custom-scss .dp-test-custom-inner { color: purple; }'
        );
        $this->assertEqualCss(
            $service->getCssBlobStorage($this->edit_theme_set, 'RTL')->getData(),
            '.dp-test-custom-scss .dp-test-custom-inner { color: purple; }'
        );
    }
}
