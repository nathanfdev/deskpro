<?php

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
