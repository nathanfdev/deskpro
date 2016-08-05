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

namespace DeskPRO\Bundle\AppBundle\Settings;

use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface;
use DeskPRO\Bundle\AppBundle\Settings\Model\UpdaterSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\UpdaterStatus;
use Symfony\Component\Routing\RouterInterface;

class UpdaterSettingsResolver extends AbstractBrandAwareSettingsResolver
{
    const AUTO_UPDATER_ENABLED        = 'auto_updater_enabled';
    const AUTO_UPDATER_NEXT_TIME      = 'auto_updater_next_check';
    const AUTO_UPDATER_NEXT_IS_MANUAL = 'auto_updater_next_is_manaul';
    const AUTO_UPDATER_TIME_OF_DAY    = 'auto_updater_time_of_day';
    const AUTO_UPDATER_TIMEZONE       = 'auto_updater_time_of_day_tz';
    const AUTO_UPDATER_INTERVAL       = 'auto_updater_interval_days';

    /**
     * @var AppEnvInterface
     */
    private $appEnv;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        BrandAwareSettingsResolver $settingsResolver,
        AppEnvInterface $appEnv,
        RouterInterface $router
    ) {
        parent::__construct($settingsResolver);
        $this->appEnv = $appEnv;
        $this->router = $router;
    }

    /**
     * @return UpdaterSettings
     */
    public function getUpdaterSettings()
    {
        $updaterSettings = new UpdaterSettings();
        $updaterSettings->setIsEnabled((bool) $this->getSetting(self::AUTO_UPDATER_ENABLED));

        if ($updaterSettings->isEnabled() && !$this->getNextCheckDate()) {
            $updaterSettings->setIsEnabled(false);
        }

        $tzName = $this->getSetting(self::AUTO_UPDATER_TIMEZONE) ?: 'UTC';
        try {
            $tz = new \DateTimeZone($tzName);
        } catch (\Exception $e) {
            $tz = new \DateTimeZone('UTC');
        }
        $updaterSettings->setTimezone($tz);

        $days = max(1, (int) $this->getSetting(self::AUTO_UPDATER_INTERVAL));
        $updaterSettings->setIntervalDays($days);
        $updaterSettings->setTimeOfDay($this->getSetting(self::AUTO_UPDATER_TIME_OF_DAY));

        return $updaterSettings;
    }

    /**
     * @return UpdaterStatus
     */
    public function getUpdaterStatus()
    {
        $status = new UpdaterStatus();
        $status->setNextCheck($this->getNextCheckDate(), $this->getSetting(self::AUTO_UPDATER_NEXT_IS_MANUAL));

        $phpPath = $this->appEnv->getConfig('paths.php_path');

        // Escape if its not a simple path
        if (!preg_match('/^[a-zA-Z0-9\/\.\-_]/', $phpPath)) {
            $phpPath = escapeshellarg($phpPath);
        }

        $status->setCliCommand(
            $phpPath
            .' '
            .$this->appEnv->getDpRoot().DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'console'
            .' '
            .'dp:update'
        );

        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        if ($DP_ENV->getDatManager()->hasTxtFile('server_info_auth')) {
            $auth = $DP_ENV->getDatManager()->readTxtFile('server_info_auth');
        } else {
            $auth = '';
        }
        $status->setLogUrl($this->router->generate('serve_root', [], RouterInterface::ABSOLUTE_URL)."__serverinfo/logs/updater?auth=$auth");
        $status->setWatcherUrl($this->router->generate('serve_root', [], RouterInterface::ABSOLUTE_URL).'/admin/updater-status/'.sha1($auth.'update_watcher'));

        $status->setBackupPath($DP_ENV->getUserBackupsDir());

        return $status;
    }

    /**
     * @return \DateTime|null
     */
    private function getNextCheckDate()
    {
        if (!$this->getSetting(self::AUTO_UPDATER_ENABLED)) {
            return;
        }

        $nextDateStr = $this->getSetting(self::AUTO_UPDATER_NEXT_TIME);
        $nextDate    = null;
        if ($nextDateStr) {
            try {
                $nextDate = \DateTime::createFromFormat('Y-m-d H:i:s', $nextDateStr);
            } catch (\Exception $e) {
                $nextDate = null;
            }
        }

        return $nextDate;
    }
}
