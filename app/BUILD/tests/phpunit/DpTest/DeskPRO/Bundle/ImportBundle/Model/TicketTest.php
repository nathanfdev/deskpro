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

use DeskPRO\Bundle\ImportBundle\Model\Ticket;

/**
 * Class TicketTest.
 */
class TicketTest extends AbstractModelTest
{
    protected static $modelClass = Ticket::class;

    public function test_required_params_validation()
    {
        $errors = $this->validateData([]);

        $this->assertCount(4, $errors);
        $this->assertEquals('person', $errors[0]->getPropertyPath());
        $this->assertEquals('status', $errors[1]->getPropertyPath());
        $this->assertEquals('subject', $errors[2]->getPropertyPath());
        $this->assertEquals('raw_data', $errors[3]->getPropertyPath());
    }

    public function test_required_params()
    {
        $params = [
            'subject' => 'Ticket subject',
            'status'  => 'awaiting_agent',
            'person'  => 1,
        ];

        $this->assertEquals($this->transformData($params), array_merge($params, [
            'labels'        => [],
            'custom_fields' => [],
            'participants'  => [],
            'messages'      => [],
            'is_hold'       => false,
            'urgency'       => 1,
            'logs'          => [],
        ]));
    }

    public function test_full_params()
    {
        $params = [
            'ref'          => 'ABCDEFG',
            'subject'      => 'Ticket subject',
            'department'   => 'Department Name',
            'person'       => '1',
            'agent'        => '2',
            'status'       => 'awaiting_user',
            'labels'       => ['label 1', 'label 2'],
            'participants' => ['user_1@deskpro.dev', 'user_2@deskpro.dev'],
            'urgency'      => 5,
            'is_hold'      => true,
            'logs'         => [
                [
                    'action_type' => 'free',
                    'details'     => ['message' => 'Imported from ZD'],
                ],
            ],
            'date_created'  => '2016-07-15T12:55:01+0300',
            'date_archived' => '2016-07-15T12:55:01+0300',
            'date_resolved' => '2016-07-15T12:55:01+0300',
            'category'      => 'Ticket category 1',
            'workflow'      => 'Ticket workflow 1',
            'product'       => 'Ticket product 1',
            'language'      => 'eng',
            'organization'  => 'Org 1',
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
            'messages' => [
                [
                    'oid'          => '1',
                    'person'       => '1',
                    'message'      => 'ticket message',
                    'format'       => 'html',
                    'is_note'      => true,
                    'date_created' => '2016-07-15T12:55:01+0300',
                    'attachments'  => [
                        [
                            'person'       => '1',
                            'blob_url'     => 'http://url',
                            'file_name'    => 'file.jpg',
                            'content_type' => 'image/jpg',
                            'is_inline'    => true,
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($this->transformData($params), $params);
    }
}
