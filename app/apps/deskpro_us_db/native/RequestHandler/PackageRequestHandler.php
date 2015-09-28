<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 *
 * @category Entities
 */
namespace deskpro_us_db\RequestHandler;

use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestContext;
use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestHandlerInterface;
use Application\DeskPRO\Usersource\UsersourceTester;
use deskpro_us_db\Usersource\AppOptionsMapper;

class PackageRequestHandler implements ApiPackageRequestHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handleApiPackageRequest(ApiPackageRequestContext $context)
    {
        switch ($context->getAction()) {
            case 'get-drivers':
                return $this->getAvailableDriversAction($context);
                break;
            case 'test-settings':
                return $this->testSettingsAction($context);
                break;
            default:
                throw $context->createNotFoundException();
        }
    }

    /**
     * @param ApiPackageRequestContext $context
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function testSettingsAction(ApiPackageRequestContext $context)
    {
        $username = $context->getIn()->getString('username');
        $password = $context->getIn()->getString('password');
        $options  = AppOptionsMapper::getOptions($context->getIn()->getCleanValueArray('settings'));

        if (defined('dpc_is_cloud')) {
            if ($app_id = $context->getIn()->getString('app_id')) {
                $app                     = $context->getContainer()->getAppManager()->getApp($app_id);
                $options['password_php'] = $app->getSetting('php_code');
            } else {
                $options['password_php'] = '';
            }
        }

        $tester = UsersourceTester::createFromOptions('Application\\Deskpro\\Usersource\\Adapter\\DbTablePhpPasswordCheck', $options);
        $tester->test($username, $password);

        $result_data = array(
            'log'      => $tester->getLog(),
            'raw_data' => $tester->getRawData(),
            'is_valid' => $tester->isValid(),
        );

        return $context->createJsonResponse($result_data);
    }

    /**
     * @param ApiPackageRequestContext $context
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAvailableDriversAction(ApiPackageRequestContext $context)
    {

        /*
         * null means installed, "description" - not installed
         */
        $drivers = array(
            'pdo_mysql'  => 'You have to install PDO_MYSQL database driver. <a href="http://php.net/manual/en/ref.pdo-mysql.php">http://php.net/manual/en/ref.pdo-mysql.php</a>',
            'pdo_pgsql'  => 'You have to install PDO_PGSQL database driver. <a href="http://php.net/manual/en/ref.pdo-pgsql.php">http://php.net/manual/en/ref.pdo-pgsql.php</a>',
            'pdo_sqlite' => 'You have to install PDO_SQLITE database driver. <a href="http://php.net/manual/en/ref.pdo-sqlite.php">http://php.net/manual/en/ref.pdo-sqlite.php</a>',
            'pdo_odbc'   => 'You have to install PDO_ODBC database driver. <a href="http://php.net/manual/en/ref.pdo-odbc.php">http://php.net/manual/en/ref.pdo-odbc.php</a>',
            'sqlsrv'     => 'You have to install SQLSRV database driver. <a href="http://php.net/manual/en/sqlsrv.installation.php">http://php.net/manual/en/sqlsrv.installation.php</a>',
            'oci8'       => 'You have to install OCI8 extension. <a href="http://php.net/manual/en/ref.pdo-odbc.php">http://php.net/manual/en/ref.pdo-odbc.php</a>',
        );

        $pdo = \PDO::getAvailableDrivers();
        if (in_array('mysql', $pdo)) {
            $drivers['pdo_mysql'] = null;
        }
        if (in_array('pgsql', $pdo)) {
            $drivers['pdo_pgsql'] = null;
        }
        if (in_array('sqlite', $pdo)) {
            $drivers['pdo_sqlite'] = null;
        }
        if (in_array('odbc', $pdo)) {
            $drivers['pdo_odbc'] = null;
        }
        if (function_exists('sqlsrv_connect')) {
            $drivers['sqlsrv'] = null;
        }
        if (function_exists('oci_connect')) {
            $drivers['oci8'] = null;
        }

        return $context->createJsonResponse($drivers);
    }
}
