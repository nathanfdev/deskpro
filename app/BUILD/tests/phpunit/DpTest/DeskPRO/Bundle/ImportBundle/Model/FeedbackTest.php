<?php

namespace DpTest\DeskPRO\Bundle\ImportBundle\Model;

use DeskPRO\Bundle\ImportBundle\Model\Feedback;

/**
 * Class FeedbackTest.
 */
class FeedbackTest extends AbstractModelTest
{
    protected static $modelClass = Feedback::class;

    public function test_required_params_validation()
    {
        $errors = $this->validateData([]);

        $this->assertCount(4, $errors);
        $this->assertEquals('title', $errors[0]->getPropertyPath());
        $this->assertEquals('content', $errors[1]->getPropertyPath());
        $this->assertEquals('status', $errors[2]->getPropertyPath());
        $this->assertEquals('raw_data', $errors[3]->getPropertyPath());
    }

    public function test_required_params()
    {
        $params = [
            'title'   => 'Feedback 1',
            'content' => 'Feedback content',
            'status'  => 'closed',
        ];

        $this->assertEquals($this->transformData($params), array_merge($params, [
            'labels'        => [],
            'view_count'    => 0,
            'popularity'    => 0,
            'attachments'   => [],
            'custom_fields' => [],
        ]));
    }

    public function test_full_params()
    {
        $params = [
            'title'          => 'Feedback 1',
            'content'        => 'Feedback content',
            'language'       => 'eng',
            'status'         => 'active',
            'date_created'   => '2016-07-15T12:55:01+0300',
            'date_published' => '2016-07-15T12:55:01+0300',
            'category'       => 'feedback category',
            'person'         => '1',
            'labels'         => ['label 1', 'label 2'],
            'view_count'     => 20,
            'popularity'     => 10,
            'attachments'    => [
                [
                    'person'       => '1',
                    'blob_url'     => 'http://url',
                    'file_name'    => 'file.jpg',
                    'content_type' => 'image/jpg',
                    'is_inline'    => true,
                ],
            ],
            'custom_fields' => [
                [
                    'oid'   => 1,
                    'value' => 'val',
                ],
                [
                    'name'  => 'Field 1',
                    'value' => 'val 2',
                ],
            ],
        ];

        $this->assertEquals($this->transformData($params), $params);
    }
}
