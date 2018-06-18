<?php

namespace DpTest\DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppBundleAdapters;

use Application\DeskPRO\DependencyInjection\SystemServices\ZipperService;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppBundleAdapters\PclzipBundleWriter;
use DpTest\DeskProTestCase;

class PclzipBundleTest extends DeskProTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        ZipperService::requirePclzip();
    }

    /**
     * @test
     */
    public function retrieve_contents_of_manifest()
    {
        $manifestContents = 'dummy manifest contents';
        $zipArchiveBundle = PclzipBundleWriter::fromTmp()->setManifest($manifestContents)->build();

        $actualManifestContents = $zipArchiveBundle->getManifestAsString();
        $this->assertEquals($manifestContents, $actualManifestContents, 'retrieving the contents of a missing manifest should return null');
    }

    /**
     * @test
     */
    public function retrieve_the_contents_of_a_missing_manifest_should_return_null()
    {
        $zipArchiveBundle = PclzipBundleWriter::fromTmp()->addFile(__FILE__)->build();

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

        $zipArchiveBundle   = PclzipBundleWriter::fromTmp()->addFolder($wwwRoot, 2)->build();
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
        $this->assertGreaterThan(0, count($filePathList));
        $this->assertEquals($filePathList, $actualFilePathList, 'unexpected list of app bundle files');
    }

}
