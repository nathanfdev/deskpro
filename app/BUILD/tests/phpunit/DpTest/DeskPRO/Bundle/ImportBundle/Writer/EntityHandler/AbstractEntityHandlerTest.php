<?php

namespace DpTest\DeskPRO\Bundle\ImportBundle\Writer\EntityHandler;

use DeskPRO\Bundle\ImportBundle\Model;
use DeskPRO\Bundle\ImportBundle\Writer\ModelWriter;
use DpTest\DeskPRO\Bundle\ImportBundle\Writer\AbstractWriterTest;
use Monolog\Handler\TestHandler;

/**
 * Class AbstractWriterTest.
 */
abstract class AbstractEntityHandlerTest extends AbstractWriterTest
{
    /**
     * @var ModelWriter
     */
    protected $writer;

    /**
     * @var TestHandler
     */
    protected $loggerHandler;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->writer = $this->getContainer()->get('dp.importer.writer');
        parent::setUp();
    }

    /**
     * @param string $filename
     * @param string $content
     *
     * @return Model\Attachment
     */
    protected function createAttachmentModel($filename = 'file1.text', $content = 'attachment content')
    {
        $model = new Model\Attachment();
        $model->setBlobData($content);
        $model->setFileName($filename);
        $model->setContentType('text/plain');

        return $model;
    }
}
