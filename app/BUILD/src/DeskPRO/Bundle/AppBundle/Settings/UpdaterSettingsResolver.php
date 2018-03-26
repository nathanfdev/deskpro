<?php

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
        // If a path contains php-win.exe, rewrite it to php.exe.
        $phpPath = str_replace('php-win.exe', 'php.exe', $phpPath);
        // Also if the path contains special characters like spaces or parenthesis, we need to quote it. E.g. "C:\....\php.exe" dp:update-db
        if (preg_match('/[^a-zA-Z0-9\\\:\/\.\-_]/', $phpPath)) {
            $phpPath = '"'.$phpPath.'"';
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
        $status->setLogUrl(
            $this->router->generate('serve_root', [], RouterInterface::ABSOLUTE_URL)
            ."__serverinfo/logs/updater?auth=$auth"
        );
        $status->setWatcherUrl(
            $this->router->generate('serve_root', [], RouterInterface::ABSOLUTE_URL).'admin/updater-status/'
            .sha1($auth.'update_watcher')
        );

        $status->setBackupPath($DP_ENV->getUserBackupsDir());

        return $status;
    }

    /**
     * @return \DateTime|null
     */
    private function getNextCheckDate()
    {
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
