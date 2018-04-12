<?php

namespace DpTest\DeskPRO\Bundle\ImportBundle\Model;

use DeskPRO\Bundle\ImportBundle\Model\Download;

/**
 * Class DownloadTest.
 */
class DownloadTest extends AbstractModelTest
{
    protected static $modelClass = Download::class;

    public function test_required_params_validation()
    {
        $errors = $this->validateData([]);

        $this->assertCount(5, $errors);
        $this->assertEquals('blob', $errors[0]->getPropertyPath());
        $this->assertEquals('title', $errors[1]->getPropertyPath());
        $this->assertEquals('content', $errors[2]->getPropertyPath());
        $this->assertEquals('status', $errors[3]->getPropertyPath());
        $this->assertEquals('raw_data', $errors[4]->getPropertyPath());
    }

    public function test_required_params()
    {
        $params = [
            'title'   => 'Download 1',
            'content' => 'Download content',
            'status'  => 'published',
            'blob'    => [
                'blob_url'     => 'http://url',
                'file_name'    => 'file.jpg',
                'content_type' => 'image/jpg',
            ],
        ];

        $this->assertEquals($this->transformData($params), array_merge($params, [
            'view_count'    => 0,
            'num_downloads' => 0,
            'labels'        => [],
        ]));
    }

    public function test_full_params()
    {
        $params = [
            'title'   => 'Download 1',
            'content' => 'Download content',
            'status'  => 'archived',
            'person'  => '1',
            'blob'    => [
                'blob_url'     => 'http://url',
                'file_name'    => 'file.jpg',
                'content_type' => 'image/jpg',
            ],
            'view_count'     => 10,
            'num_downloads'  => 20,
            'labels'         => ['label 1', 'label 2'],
            'category'       => 'Download category 1',
            'language'       => 'eng',
            'date_created'   => '2016-07-15T12:55:01+0300',
            'date_published' => '2016-07-15T12:55:01+0300',
        ];

        $this->assertEquals($this->transformData($params), $params);
    }
}
