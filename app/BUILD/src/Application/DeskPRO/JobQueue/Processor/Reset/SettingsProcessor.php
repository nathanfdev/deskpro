<?php

namespace Application\DeskPRO\JobQueue\Processor\Reset;

use Doctrine\DBAL\Connection;

class SettingsProcessor extends Base
{
    const JOB_TYPE = 'reset.settings';

    const NAMES = 'core.settings.names';

    protected $skip = [
        'core.site_url',
        'core.deskpro_build',
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
        $this->connection->beginTransaction();

        try {
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

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
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
