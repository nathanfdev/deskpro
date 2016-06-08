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

namespace Application\DeskPRO\JobQueue\Processor\Reset;

use Doctrine\DBAL\Connection;

class SettingsProcessor extends Base
{
    const JOB_TYPE = 'reset.settings';

    const NAMES = 'core.settings.names';

    protected $skip = [
        'core.site_url',
        'core.deskpro_build',
        'core.deskpro_build_num',
        'core.deskpro_version',
        'core.last_cron_run',
        'core.last_cron_start',
        'core.last_heartbeat',
        'core.license',
    ];

    /**
     * {@inheritdoc}
     */
    protected function doProcess(array $data)
    {
        $enc = $this->connection->fetchColumn(
            'select value from settings where name = :name',
            ['name' => self::NAMES]
        );
        if (!$settings = json_decode($enc, 1)) {
            throw new \Exception('Settings backup not found');
        }
        foreach ($this->skip as $k) {
            $settings[$k] = null;
        }
        $settings[self::NAMES] = $enc;

        $this->connection->executeUpdate(
            'delete from settings where name not in (:names)',
            ['names' => array_keys($settings)],
            ['names' => Connection::PARAM_STR_ARRAY]
        );

        foreach ($this->skip as $k) {
            unset($settings[$k]);
        }

        foreach ($settings as $k => $v) {
            $this->connection->executeUpdate(
                'replace into settings values (:name, :value)',
                ['name' => $k, 'value' => $v]
            );
        }
    }

    public static function saveBaseSettings(Connection $connection)
    {
        $settings = [];
        foreach ($connection->fetchAll('select * from settings') as $row) {
            $settings[$row['name']] = $row['value'];
        }
        $connection->executeUpdate(
            'replace into settings values (:name, :value)',
            ['name' => self::NAMES, 'value' => json_encode($settings)]
        );
    }
}
