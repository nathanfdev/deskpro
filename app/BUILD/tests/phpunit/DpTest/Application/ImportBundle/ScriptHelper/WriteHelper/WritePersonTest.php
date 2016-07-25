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

/**
 * Class WritePersonTest.
 */
class WritePersonTest extends AbstractWriteHelperTest
{
    private $requiredPersonParams = [
        'name'   => 'Person Name',
        'emails' => ['email_1@deskpro.dev'],
    ];

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage person #1 validation is failed
     */
    public function test_check_validation()
    {
        $this->writer->writePerson(1, []);
    }

    public function test_required_params()
    {
        $this->writer->writePerson(1, $this->requiredPersonParams);
        $this->assertImporterModelEquals('/1/person/1.json', array_merge($this->requiredPersonParams, [
            'is_agent'      => false,
            'is_admin'      => false,
            'is_disabled'   => false,
            'is_deleted'    => false,
            'labels'        => [],
            'user_groups'   => [],
            'agent_groups'  => [],
            'custom_fields' => [],
        ]));
    }

    public function test_full_params()
    {
        $params = [
            'is_agent'              => true,
            'is_admin'              => true,
            'is_disabled'           => true,
            'is_deleted'            => true,
            'first_name'            => 'First Name',
            'last_name'             => 'Last Name',
            'name'                  => 'Person Name',
            'emails'                => ['email_1@deskpro.dev'],
            'override_display_name' => 'Override name',
            'password'              => 'password',
            'password_scheme'       => 'bcrypt',
            'labels'                => ['label 1', 'label 2'],
            'contact_data'          => [
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
            'user_groups'           => ['everyone'],
            'agent_groups'          => ['all_perm'],
            'timezone'              => 'UTC',
            'date_created'          => '2016-07-15T12:55:01+0300',
            'language'              => 'eng',
            'organization'          => '1',
            'organization_position' => 'dev',
            'custom_fields'         => [
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

        $this->writer->writePerson(1, $params);
        $this->assertImporterModelEquals('/1/person/1.json', $params);
    }
}
