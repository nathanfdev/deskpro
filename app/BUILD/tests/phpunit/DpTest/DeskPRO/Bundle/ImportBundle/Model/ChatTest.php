<?php

namespace DpTest\DeskPRO\Bundle\ImportBundle\Model;

use DeskPRO\Bundle\ImportBundle\Model\Chat;

/**
 * Class ChatTest.
 */
class ChatTest extends AbstractModelTest
{
    protected static $modelClass = Chat::class;

    public function test_required_params_validation()
    {
        $errors = $this->validateData([]);

        $this->assertCount(3, $errors);
        $this->assertEquals('messages', $errors[0]->getPropertyPath());
        $this->assertEquals('endedBy', $errors[1]->getPropertyPath());
        $this->assertEquals('raw_data', $errors[2]->getPropertyPath());
    }

    public function test_required_params()
    {
        $params = [
            'ended_by' => 'timeout',
            'messages' => [
                [
                    'oid'          => 1,
                    'person'       => 1,
                    'date_created' => '2016-07-15T14:55:01+0300',
                    'content'      => 'message text',
                ],
            ],
        ];

        $this->assertEquals($this->transformData($params), array_merge($params, [
            'subject'        => '',
            'labels'         => [],
            'custom_fields'  => [],
            'rating_overall' => 0,
            'rating_comment' => '',
        ]));
    }

    public function test_full_params()
    {
        $params = [
            'subject'        => 'Chat subject',
            'person'         => 1,
            'agent'          => 2,
            'date_created'   => '2016-07-15T12:55:01+0300',
            'date_ended'     => '2016-07-15T14:55:01+0300',
            'ended_by'       => 'agent',
            'rating_overall' => 5,
            'rating_comment' => 'Some comment',
            'labels'         => ['label1', 'label2'],
            'custom_fields'  => [
                [
                    'oid'   => 1,
                    'value' => 'val',
                ],
                [
                    'name'  => 'Field 1',
                    'value' => 'val 2',
                ],
            ],
            'messages' => [
                [
                    'oid'          => 1,
                    'person'       => 1,
                    'date_created' => '2016-07-15T14:55:01+0300',
                    'content'      => 'message text',
                ],
                [
                    'oid'          => 2,
                    'person'       => 2,
                    'date_created' => '2016-07-15T14:55:01+0300',
                    'content'      => 'message text',
                ],
            ],
        ];

        $this->assertEquals($this->transformData($params), $params);
    }
}
