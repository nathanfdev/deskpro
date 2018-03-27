<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\UpdateBundle\Distro;

use DeskPRO\Bundle\UpdateBundle\Distro\Manifest\DistroRelease;
use DeskPRO\Bundle\UpdateBundle\Distro\Manifest\DistroReleaseCollection;
use DeskPRO\Bundle\UpdateBundle\Instance\InstanceReader;
use DeskPRO\Bundle\UpdateBundle\Instance\InstanceStatus;
use DpTest\DeskProTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class InstanceStatusTest extends DeskProTestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    /**
     * @var DistroReleaseCollection
     */
    private $releases;

    public function setUp()
    {
        $this->root = vfsStream::setup('instance_test');
        vfsStream::create(require(__DIR__.'/../data/app_structure.php'), $this->root);

        $data           = json_decode(file_get_contents(__DIR__.'/../data/example_manifest.json'), true);
        $this->releases = new DistroReleaseCollection(array_map(function ($r) {
            return new DistroRelease($r);
        }, $data['releases']));
    }

    /**
     * @return InstanceReader
     */
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
     * @return InstanceStatus
     */
    private function getInstanceStatus()
    {
        $reader = $this->getInstanceReader();
        $status = $reader->getInstanceStatus($this->releases);

        return $status;
    }

    /**
     * @test
     */
    public function it_instantiates()
    {
        $this->getInstanceStatus();
    }

    /**
     * @test
     */
    public function it_has_correct_releases()
    {
        $status = $this->getInstanceStatus();
        $this->assertEquals('15741.0', $status->getCurrentRelease()->getId(), 'Current release');
        $this->assertEquals('15742.0', $status->getLatestRelease()->getId(), 'Latest release');
        $this->assertTrue($status->isOutdated());
    }

    /**
     * @test
     */
    public function it_has_correct_counts()
    {
        $status = $this->getInstanceStatus();
        $this->assertEquals(1, $status->getNumBetween(), 'Number of releases between');
        $this->assertEquals(1, $status->getDaysOld(), 'Number of days since last release');
    }
}
