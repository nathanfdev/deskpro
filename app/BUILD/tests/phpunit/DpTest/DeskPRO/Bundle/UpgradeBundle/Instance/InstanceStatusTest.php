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
