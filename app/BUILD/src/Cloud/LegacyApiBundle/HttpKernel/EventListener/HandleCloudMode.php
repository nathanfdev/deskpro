<?php

namespace Cloud\LegacyApiBundle\HttpKernel\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class HandleCloudMode implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    private static $disableRoutes = [
        'api_dp_license_save',
        'api_dp_keyfile',
        'api_dp_license_versioninfo',
        'api_dp_license_latestversion',
        'api_dp_license_news',
        'api_all_settings_raw',
        'api_all_settings_raw_save',
        'api_server_settings',
        'api_server_settings_save',
        'api_server_reqs',
        'api_server_php_info',
        'api_server_mysql_info',
        'api_server_mysql_info_schemadiff',
        'api_server_mysql_status',
        'api_server_mysql_sort_order',
        'api_server_mysql_sort_order_save',
        'api_server_mysql_sort_order_status',
        'api_server_cron_status',
        'api_server_error_status',
        'api_server_delete_error_log',
        'api_server_apc_status',
        'api_server_autoupdate_begin',
        'api_server_autoupdate_abort',
        'api_server_autoupdate_status',
        'api_server_error_logs',
        'api_server_error_logs_get',
        'api_server_error_logs_delete',
        'api_server_cron',
        'api_server_cron_logs',
        'api_server_cron_logs_delete',
        'api_server_file_uploads',
        'api_server_test_file_uploads',
        'api_server_switch_file_uploads_storage',
        'api_server_switch_file_uploads_storage_status',
        'api_server_file_check',
        'api_server_file_check_get',
        'api_server_report_file_get',
        'api_server_report_file_check_save'
    ];

    private static $ctrlMap = [
        \Application\LegacyApiBundle\Controller\AgentsController::class => \Cloud\LegacyApiBundle\Controller\AgentsController::class,
        \Application\LegacyApiBundle\Controller\LicenseController::class => \Cloud\LegacyApiBundle\Controller\LicenseController::class,
        \Application\LegacyApiBundle\Controller\SettingsController::class => \Cloud\LegacyApiBundle\Controller\SettingsController::class,
        \Application\LegacyApiBundle\Controller\EmailAccountsController::class => \Cloud\LegacyApiBundle\Controller\EmailAccountsController::class,
    ];

    /**
     * HandleCloudMode constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $route = $request->attributes->get('_route');
        $ctrl = $request->attributes->get('_controller');

        if ($route && in_array($route, self::$disableRoutes)) {
            $this->logger->info("Route '$route' is disabled on cloud");
            $event->setResponse(new Response('API route not found', 404));
            return;
        }

        if ($ctrl && is_string($ctrl) && strpos($ctrl, '::') !== false) {
            list ($className, $action) = explode('::', $ctrl, 2);
            if (isset(self::$ctrlMap[$className])) {
                $newClassName = self::$ctrlMap[$className];
                $this->logger->info("Rewrite ctrl class '$className' to '$newClassName'");
                $request->attributes->set('_controller', "$newClassName::$action");
            }
        }
    }

    public static function getSubscribedEvents()
    {
        if (!defined('DPC_IS_CLOUD')) {
            return [];
        }

        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 30)),
        );
    }
}
