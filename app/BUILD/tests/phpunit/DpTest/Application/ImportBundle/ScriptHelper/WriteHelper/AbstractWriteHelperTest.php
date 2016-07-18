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

namespace DpTest\Application\ImportBundle\ScriptHelper\WriteHelper;

use Application\ImportBundle\ScriptHelper\WriteHelper;
use DpTest\ApiTestCase;
use DpTestSrc\TestBundle\Filesystem;
use Symfony\Component\Debug\BufferingLogger;

/**
 * Class AbstractWriteHelperTest.
 */
abstract class AbstractWriteHelperTest extends ApiTestCase
{
    /**
     * @var string
     */
    protected $tmpDir;

    /**
     * @var WriteHelper
     */
    protected $writer;

    /**
     * @var BufferingLogger
     */
    protected $logger;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->tmpDir = __DIR__.'/tmp';
        $this->logger = new BufferingLogger();
        $this->writer = new WriteHelper(
            $this->getContainer()->get('serializer'),
            $this->getContainer()->get('validator'),
            $this->getContainer()->get('form_error.validator_errors_generator.api'),
            $this->logger
        );

        $this->writer->setOutputPath($this->tmpDir);
        Filesystem::cleanDir($this->tmpDir);
    }

    /**
     * @param string $filePath
     * @param array  $params
     */
    protected function assertImporterModelEquals($filePath, array $params)
    {
        $filePath = $this->tmpDir.$filePath;
        $this->assertFileExists($filePath);

        $actual = json_decode(file_get_contents($filePath), true);
        $this->assertEquals($params, $actual);
    }
}
