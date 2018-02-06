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
