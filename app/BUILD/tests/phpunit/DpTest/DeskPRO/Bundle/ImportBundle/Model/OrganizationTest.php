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

use DeskPRO\Bundle\ImportBundle\Model\Organization;

/**
 * Class OrganizationTest.
 */
class OrganizationTest extends AbstractModelTest
{
    protected static $modelClass = Organization::class;

    public function test_required_params_validation()
    {
        $errors = $this->validateData([]);

        $this->assertCount(2, $errors);
        $this->assertEquals('name', $errors[0]->getPropertyPath());
        $this->assertEquals('raw_data', $errors[1]->getPropertyPath());
    }

    public function test_required_params()
    {
        $params = [
            'name' => 'Org name',
        ];

        $this->assertEquals($this->transformData($params), array_merge($params, [
            'labels'        => [],
            'custom_fields' => [],
            'email_domains' => [],
        ]));
    }

    public function test_full_params()
    {
        $params = [
            'name'    => 'Org name',
            'picture' => [
                'blob_url'     => 'http://url',
                'file_name'    => 'file.jpg',
                'content_type' => 'image/jpg',
            ],
            'importance'    => '5',
            'date_created'  => '2016-07-15T12:55:01+0300',
            'labels'        => ['label 1', 'label 2'],
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
            'email_domains' => ['domain1.com', 'domain2.com'],
            'contact_data'  => [
                'address' => [
                    [
                        'address' => 'address',
                        'state'   => 'state',
                        'zip'     => 'zip',
                        'city'    => 'city',
                        'country' => 'country',
                    ],
                ],
                'facebook' => [
                    [
                        'url' => 'http://facebook.com',
                    ],
                ],
                'instant_message' => [
                    [
                        'service'  => 'skype',
                        'username' => 'myname',
                    ],
                    [
                        'service'  => 'icq',
                        'username' => 'myname',
                    ],
                ],
                'linked_in' => [
                    [
                        'url' => 'http://url',
                    ],
                ],
                'phone' => [
                    [
                        'number' => '+14157012311',
                        'type'   => 'fax',
                    ],
                ],
                'twitter' => [
                    [
                        'username'     => 'myname',
                        'display_feed' => '1',
                    ],
                ],
                'website' => [
                    [
                        'url' => 'http://url',
                    ],
                ],
            ],
        ];

        $this->assertEquals($this->transformData($params), $params);
    }
}
