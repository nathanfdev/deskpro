<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use Application\DeskPRO\Entity\ApiKey;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use DeskPRO\Bundle\AppBundle\Entity\ApiKeyAction;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * This inserts some default settings so you dont have to waste time going through the welcome wizard.
 */
class DevSetupFixture extends AbstractDpFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 20;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        if (file_exists(DP_ROOT.'/sys/config/installer-type')) {
            $f = trim(@file_get_contents(DP_ROOT.'/sys/config/installer-type'));
            // buildserver has its own config
            if ($f === 'buildserver') {
                return;
            }
        }

        $this->db->deleteIn('settings',
            [
                'core.done_data_initializer',
                'core.deskpro_url',
                'core.deskpro_name',
                'core.default_timezone',
                'core.license',
                'core.setup_initial',
                'admin_has_loaded',
            ],
            'name'
        );

        if (file_exists(DP_WEB_ROOT.'/config/LOCALHOST_URL.txt')) {
            $url = rtrim(trim(file_get_contents(DP_WEB_ROOT.'/config/LOCALHOST_URL.txt')), '/').'/';
        } else {
            $url = 'http://deskpro-dev/';
        }

        $ins = [
            ['name' => 'core.done_data_initializer', 'value' => 1],
            ['name' => 'core.setup_initial', 'value' => 1],
            ['name' => 'admin_has_loaded', 'value' => 1],
            ['name' => 'core.default_timezone', 'value' => 'UTC'],
            ['name' => 'core.deskpro_name', 'value' => 'Helpdesk'],
            ['name' => 'core.deskpro_url', 'value' => $url],
            ['name' => 'core.license', 'value' => @file_get_contents(DP_DIR.'/dev/dev-lic-key.txt') ?: ''],
        ];

        $this->db->batchInsert('settings', $ins);

        $this->db->insert(
            'email_accounts',
            [
                'account_type'     => 'tickets',
                'incoming_account' => json_encode(
                    [
                        '@CLASS' => 'Application\\DeskPRO\\Email\\EmailAccount\\IncomingAccount\\Pop3Config',
                        '@DATA'  => [
                            'host'     => 'pop.example.com',
                            'port'     => '110',
                            'user'     => 'dev@deskprodev.com',
                            'password' => 'bogus',
                        ],
                    ]
                ),
                'outgoing_account' => json_encode(
                    [
                        '@CLASS' => 'Application\\DeskPRO\\Email\\EmailAccount\\OutgoingAccount\\PhpMailConfig',
                        '@DATA'  => ['PhpMail' => true],
                    ]
                ),
                'is_enabled'         => 1,
                'address'            => 'dev@deskprodev.com',
                'other_addresses'    => null,
                'options'            => null,
                'date_created'       => date('Y-m-d H:i:s'),
                'date_read_start'    => date('Y-m-d H:i:s'),
                'date_last_incoming' => null,
                'is_read_active'     => 0,
            ]
        );

        // default api key for administrator
        $person      = $manager->find(Person::class, 1);
        $key         = new ApiKey();
        $key->person = $person;
        $key->code   = 'dev-admin-code';
        $key->note   = 'dev-admin-code';
        $keyAction   = new ApiKeyAction();
        $keyAction->setKey($key)->setAction('*');
        $manager->persist($key);
        $manager->persist($keyAction);
        $manager->flush();
    }
}
