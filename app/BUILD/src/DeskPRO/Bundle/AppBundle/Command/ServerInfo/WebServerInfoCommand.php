<?php

namespace DeskPRO\Bundle\AppBundle\Command\ServerInfo;

use DpRun\LowUtil;
use Orb\Util\Strings;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WebServerInfoCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dp:web-server-info');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var \DpRun\DpEnv */
        global $DP_ENV;

        if (!$DP_ENV->getDatManager()->hasTxtFile('server_info_auth')) {
            $DP_ENV->getDatManager()->writeTxtFile('server_info_auth', Strings::random(30, Strings::CHARS_ALPHANUM_IU));
        }

        self::initPhpinfoCli();

        $auth    = $DP_ENV->getDatManager()->readTxtFile('server_info_auth');
        $baseUrl = $DP_ENV->getConfig('settings.core.deskpro_url', null);

        if (!$baseUrl) {
            $dbConfig = $DP_ENV->getConfig('database');
            if ($dbConfig) {
                $dbInfo = LowUtil::getMysqlInfoFromConfigArray($dbConfig);
                try {
                    $pdo            = LowUtil::getPdoFromMysqlInfo($dbInfo);
                    $defaultBrandId = $pdo->query("SELECT value FROM settings WHERE name = 'portal.default_brand'")
                        ->fetchColumn();
                    if (empty($defaultBrandId)) {
                        $defaultBrandId = 1;
                    }
                    $baseUrl = $pdo->query("SELECT value FROM settings_brand
                        WHERE name = 'core.deskpro_url' AND brand_id = ".(int) $defaultBrandId)
                        ->fetchColumn();
                } catch (\Exception $e) {
                    $baseUrl = null;
                }
            }
        }

        if (!$baseUrl) {
            $baseUrl = 'http://your-url/';
        }

        $baseUrl = rtrim($baseUrl, '/');

        /** @var \Symfony\Component\Console\Helper\TableHelper $table */
        $table = $this->getHelper('table');
        $table->setLayout(TableHelper::LAYOUT_BORDERLESS);
        $table->setHeaders(['Script', 'Path']);
        $table->addRow(['PHP Info', "$baseUrl/__serverinfo/phpinfo?auth=$auth"]);
        $table->addRow(['PHP Info (CLI)', "$baseUrl/__serverinfo/phpinfo-cli?auth=$auth"]);
        $table->addRow(['Requirements Check', "$baseUrl/__serverinfo/check_requirements?auth=$auth"]);
        $table->addRow(['OpCache Status', "$baseUrl/__serverinfo/opcache?auth=$auth"]);
        $table->addRow(['DeskPRO Error Log', "$baseUrl/__serverinfo/logs/errors?auth=$auth"]);
        $table->addRow(['PHP Error Log', "$baseUrl/__serverinfo/logs/php-errors?auth=$auth"]);
        $table->addRow(['Updater Status', "$baseUrl/admin/updater-status/$auth"]);
        $table->render($output);

        return 0;
    }

    private static function initPhpinfoCli()
    {
        /* @var \DpRun\DpEnv */
        global $DP_ENV;

        ob_start();
        phpinfo();
        $phpinfo = ob_get_clean();

        @file_put_contents(
            $DP_ENV->getUserCacheDir().'/cli-phpinfo.html',
            $phpinfo
        );
    }
}
