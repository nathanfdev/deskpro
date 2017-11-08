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

namespace DpTest\DeskPRO\Bundle\AppStoreBundle\Infrastructure;

use DeskPRO\Bundle\AppStoreBundle\Infrastructure;
use DpTest\DeskProTestCase;

class AppZipArchiveBundleTest extends DeskProTestCase
{
    /**
     * @test
     */
    public function retrieve_contents_of_manifest()
    {
        $manifestContents = 'dummy manifest contents';
        $zipArchiveBundle = Infrastructure\AppZipBundleBuilder::fromTmp()->setManifest($manifestContents)->build();

        $actualManifestContents = $zipArchiveBundle->getManifestAsString();
        $this->assertEquals($manifestContents, $actualManifestContents, 'retrieving the contents of a missing manifest should return null');
    }

    /**
     * @test
     */
    public function retrieve_the_contents_of_a_missing_manifest_should_return_null()
    {
        $zipArchiveBundle = Infrastructure\AppZipBundleBuilder::fromTmp()->addFile(__FILE__)->build();

        $manifestString = $zipArchiveBundle->getManifestAsString();
        $this->assertNull($manifestString, 'retrieving the contents of a missing manifest should return null');

        //clean up
        unlink($zipArchiveBundle->getFilePath());
    }

    /**
     * @test
     */
    public function returns_a_list_of_all_the_bundled_files()
    {
        global $DP_ENV;
        $wwwRoot = $DP_ENV->getWwwRoot();

        $zipArchiveBundle   = Infrastructure\AppZipBundleBuilder::fromTmp()->addFolder($wwwRoot, 2)->build();
        $actualFilePathList = [];
        $resourceObjects    = $zipArchiveBundle->listAllResources();
        foreach ($resourceObjects as $object) {
            $actualFilePathList[] = $object->getPath();
        }
        //clean up
        unlink($zipArchiveBundle->getFilePath());

        $filePathList = [];
        $iterator     = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($wwwRoot), \RecursiveIteratorIterator::LEAVES_ONLY);
        $iterator->setMaxDepth(2);
        foreach ($iterator as $name => $file) {
            if (!$file->isDir()) { //only add files

                $localName = substr($file, strlen($wwwRoot));
                $localName = ltrim($localName, '/');

//                $filePathList[] = $file->getRealPath();
                $filePathList[] = $localName;
            }
        }

        $this->assertEquals($filePathList, $actualFilePathList, 'unexpected list of app bundle files');
    }
}
