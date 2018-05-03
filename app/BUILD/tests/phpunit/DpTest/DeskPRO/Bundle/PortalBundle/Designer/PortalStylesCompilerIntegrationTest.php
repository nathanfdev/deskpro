<?php

namespace DpTest\DeskPRO\Bundle\PortalBundle\Designer;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
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
     * @var DeskproBlobStorage
     */
    private $blobStorage;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->blobStorage = $this->getContainer()->get('blob.storage');

        $this->edit_theme_set = new ThemeSet();
        $this->edit_theme_set->setThemeId('edit_theme_set_id');
        $this->getEntityManager()->persist($this->edit_theme_set);
        $this->getEntityManager()->flush();

        $this->service = $this->createService('dummy-empty.scss');
    }

    /**
     * @param string $style       Style file path
     * @param string $custom_scss
     * @param string $mainScss
     *
     * @return PortalStylesCompiler
     */
    private function createService($style, $custom_scss = '', $mainScss = '')
    {
        return new PortalStylesCompiler(
            $this->getEntityManager(),
            $this->blobStorage,
            __DIR__."/scss/$style",
            __DIR__."/scss-rtl/$style",
            $mainScss,
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
            $this->assertNull($this->getEditThemeCss($dir));
        }
    }

    /**
     * @test
     */
    public function it_should_create_CSS_blob_on_the_edit_ThemeSet_asset()
    {
        foreach (['LTR', 'RTL'] as $dir) {
            $this->assertNull($this->getEditThemeCss($dir));
        }
        $this->service->recompile([], $this->edit_theme_set);
        foreach (['LTR', 'RTL'] as $dir) {
            $this->assertNotNull($this->getEditThemeCss($dir));
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

        $this->assertEqualCss($this->getEditThemeCss('LTR'), '.dummy-container .dummy-style { background: #fffaaa; }');
        $this->assertEqualCss($this->getEditThemeCss('RTL'), '.dummy-container .dummy-style-rtl { background: #fffaaa; }');
    }

    /**
     * @test
     */
    public function it_should_compile_color_variables()
    {
        $service = $this->createService('dummy-color.scss');
        $service->recompile(['color' => '#123456'], $this->edit_theme_set);

        $this->assertEqualCss($this->getEditThemeCss('LTR'), '.dummy-style { background: #123456; }');
        $this->assertEqualCss($this->getEditThemeCss('RTL'), '.dummy-style-rtl { background: #123456; }');
    }

    /**
     * @test
     */
    public function it_should_compile_compound_size_variables()
    {
        $service = $this->createService('dummy-size.scss');
        $service->recompile(['size' => ['value' => 42, 'unit' => '%']], $this->edit_theme_set);

        $this->assertEqualCss($this->getEditThemeCss('LTR'), '.dummy-style { margin-top: 42%; }');
        $this->assertEqualCss($this->getEditThemeCss('RTL'), '.dummy-style-rtl { margin-top: 42%; }');
    }

    /**
     * @test
     */
    public function it_should_compile_font_variables()
    {
        $service = $this->createService('dummy-font.scss');
        $service->recompile(['font' => 'Arial Test'], $this->edit_theme_set);

        $this->assertEqualCss($this->getEditThemeCss('LTR'), '.dummy-style { font: Arial Test; }');
        $this->assertEqualCss($this->getEditThemeCss('RTL'), '.dummy-style-rtl { font: Arial Test; }');
    }

    /**
     * @test
     */
    public function it_should_compile_font_when_it_is_set_to_a_custom_value()
    {
        $service = $this->createService('dummy-font.scss');
        $service->recompile(['font' => 'custom string'], $this->edit_theme_set);

        $this->assertEqualCss($this->getEditThemeCss('LTR'), '.dummy-style { font: custom string; }');
        $this->assertEqualCss($this->getEditThemeCss('RTL'), '.dummy-style-rtl { font: custom string; }');
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

        $this->assertEqualCss($this->getEditThemeCss('LTR'), '.dp-test-custom-scss .dp-test-custom-inner { color: purple; }');
        $this->assertEqualCss($this->getEditThemeCss('RTL'), '.dp-test-custom-scss .dp-test-custom-inner { color: purple; }');
    }

    /**
     * @param string $direction
     *
     * @return \DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset|null
     */
    private function getEditThemeCss($direction)
    {
        $asset = $this->service->getCssAsset($this->edit_theme_set, $direction);

        return $asset && $asset->getBlob() ? $this->blobStorage->copyBlobRecordToString($asset->getBlob()) : null;
    }
}
