<?php

namespace DpTest\DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppBundleAdapters;

use Application\DeskPRO\DependencyInjection\SystemServices\ZipperService;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppBundleAdapters\PclzipStringHandler;
use DpTest\DeskProTestCase;

class PclzipStringHandlerTest  extends DeskProTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        ZipperService::requirePclzip();
    }

    public function testWriteAndRead()
    {
        $zipFilePath = tempnam("/tmp", "FOO");
        $archive = new \PclZip($zipFilePath);
        $content = 'varooum';

        $handler = new PclzipStringHandler();
        $handler->write($archive,  $content, '/some/nested/folder/file.txt');

        $contents = $archive->listContent();
        $this->assertNotEmpty($content);
        $actualContent = $handler->read($archive, $contents[0]);
        $this->assertEquals($content, $actualContent, 'should extract the same content it initially wrote');
    }
}
