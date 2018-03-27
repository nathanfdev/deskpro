<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\UpdateBundle\Distro;

use DeskPRO\Bundle\UpdateBundle\Instance\InstanceReader;
use DpTest\DeskProTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class InstanceReaderTest extends DeskProTestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    public function setUp()
    {
        $this->root = vfsStream::setup('instance_test');
        vfsStream::create(require(__DIR__.'/../data/app_structure.php'), $this->root);
    }

    private function getInstanceReader()
    {
        return new InstanceReader(
            '15741.0',
            $this->root->getChild('app')->url(),
            $this->root->getChild('www')->url(),
            $this->root->getChild('var/kernel_cache')->url()
        );
    }

    /**
     * @test
     */
    public function it_instantiates()
    {
        $this->getInstanceReader();
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function it_throws_exception_on_invalid_path()
    {
        new InstanceReader(
            '15741.0',
            $this->root->url().'/foo',
            $this->root->getChild('www')->url(),
            $this->root->getChild('var/kernel_cache')->url()
        );
    }

    /**
     * @test
     */
    public function it_returns_correct_paths()
    {
        $inst = $this->getInstanceReader();

        $this->assertEquals($this->root->url().'/app', $inst->getAppBasePath());
        $this->assertEquals($this->root->url().'/app/FOO', $inst->getAppPath('FOO'));

        $this->assertEquals($this->root->url().'/var/kernel_cache', $inst->getKernelCacheBasePath());
        $this->assertEquals($this->root->url().'/var/kernel_cache/FOO', $inst->getKernelCachePath('FOO'));

        $this->assertEquals($this->root->url().'/www/assets', $inst->getAssetsBasePath());
        $this->assertEquals($this->root->url().'/www/assets/FOO', $inst->getAssetsPath('FOO'));
    }

    /**
     * @test
     */
    public function it_detects_build_dirs()
    {
        $inst = $this->getInstanceReader();

        $this->assertEquals([
            1465734864 => '15740.0',
            1465734865 => '15741.0',
        ], $inst->getBuilds());

        $this->assertTrue($inst->hasBuild('15740.0'));
        $this->assertFalse($inst->hasBuild('Foo'));
    }
}
