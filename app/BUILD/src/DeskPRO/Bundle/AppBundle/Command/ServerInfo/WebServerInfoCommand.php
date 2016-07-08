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
                    $pdo = LowUtil::getPdoFromMysqlInfo($dbInfo);
                    /* @TODO retrieve this value from settings_brand */
                    $baseUrl = $pdo->query("SELECT value FROM settings WHERE name = 'core.deskpro_url'")->fetchColumn();
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
        $table->addRow(['OpCache Status', "$baseUrl/__serverinfo/check_requirements?auth=$auth"]);
        $table->addRow(['DeskPRO Error Log', "$baseUrl/__serverinfo/logs/errors?auth=$auth"]);
        $table->addRow(['PHP Error Log', "$baseUrl/__serverinfo/logs/php-errors?auth=$auth"]);
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
