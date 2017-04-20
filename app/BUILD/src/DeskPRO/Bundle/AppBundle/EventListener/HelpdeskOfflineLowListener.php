<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\EventListener;

use Application\DeskPRO\Command\WorkerJobCommand;
use DeskPRO\Bundle\AppBundle\EventListener\Helper\LowTemplateHelper;
use DeskPRO\Bundle\AppBundle\Request\InterfaceInfo;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Check if the helpdesk is offline.
 *
 * Note that this checks for upgrade pending and if the helpdesk was manually shut
 * down based on a setting. There exists OfflineCheckBootTask which happens
 * before Symfony to check for low-level offline trigger.
 */
class HelpdeskOfflineLowListener implements EventSubscriberInterface
{
    // 423 - Locked
    // This is an uncommon status code. Perhaps the 'proper' code to use might be 503 unavailable,
    // but this tends to be taken as a server error (e.g., with proxies) and we don't want that
    // to happen whenever we're upgrading. Using 423 means we can handle it easily in software too.
    const OFFLINE_STATUS_CODE         = 423;
    const PENDING_UPGRADE_STATUS_CODE = 423;

    /**
     * @var InterfaceInfo
     */
    private $interfaceInfo;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $data_dir;

    /**
     * Constructor.
     *
     * @param InterfaceInfo      $interfaceInfo
     * @param ContainerInterface $container
     * @param string             $data_dir
     */
    public function __construct(InterfaceInfo $interfaceInfo, ContainerInterface $container, $data_dir)
    {
        $this->interfaceInfo = $interfaceInfo;
        $this->container     = $container;
        $this->data_dir      = $data_dir;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            // runs before everything
            KernelEvents::REQUEST  => ['onPreRequest', 2000],
            ConsoleEvents::COMMAND => ['onCommand', 2000],
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onPreRequest(GetResponseEvent $event)
    {
        if ($this->isHelpdeskOffline($event->getRequest())) {
            $event->setResponse($this->createOfflineResponse($event->getRequest()));
            $event->stopPropagation();
        } elseif ($this->isUpgradePending()) {
            $event->setResponse($this->createUpgradePendingResponse($event->getRequest()));
            $event->stopPropagation();
        }
    }

    /**
     * @param ConsoleCommandEvent $event
     */
    public function onCommand(ConsoleCommandEvent $event)
    {
        $cmd = $event->getCommand();
        if ($cmd instanceof WorkerJobCommand && ($this->isHelpdeskOffline() || $this->isUpgradePending())) {
            $event->disableCommand();

            $output = $event->getOutput();
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                if ($this->isHelpdeskOffline()) {
                    $output->writeln('<error>Helpdesk is disabled - cron will not run</error>');
                    if ($message = $this->getOfflineMessage()) {
                        $output->writeln("Message: $message");
                    }
                } else {
                    $output->writeln('<error>Upgrade is pending - cron will not run</error>');
                }
            }
        }
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    private function createOfflineResponse(Request $request)
    {
        $response = new Response();
        $response->setStatusCode(self::OFFLINE_STATUS_CODE);
        $response->headers->set('X-DeskPRO-Premature-Termintation', 'offline');

        $message = $this->getOfflineMessage();

        if (in_array('application/json', $request->getAcceptableContentTypes())) {
            $response->setContent(json_encode([
                'type'         => 'offline',
                'message_html' => $message,
                'message_text' => strip_tags($message),
            ]));
            $response->headers->set('Content-Type', 'application/json');
        } else {
            $response->setContent($this->getOfflineHtmlPage($request, $message));
            $response->headers->set('Content-Type', 'text/html');
        }

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    private function createUpgradePendingResponse(Request $request)
    {
        // users sholud just see maintenance message
        if ($this->interfaceInfo->isUserInterface()) {
            $response = $this->createOfflineResponse($request);
            // overwrite header so we can debug this response if needed
            $response->headers->set('X-DeskPRO-Premature-Termintation', 'upgrade_pending');

            return $response;
        }

        $response = new Response();
        $response->setStatusCode(self::PENDING_UPGRADE_STATUS_CODE);
        $response->headers->set('X-DeskPRO-Premature-Termintation', 'upgrade_pending');

        if (in_array('application/json', $request->getAcceptableContentTypes())) {
            $message = 'An upgrade is pending. An administrator must run the dp:update-db command.';
            $response->setContent(json_encode([
                'type'         => 'upgrade_pending',
                'message_html' => $message,
                'message_text' => $message,
            ]));
            $response->headers->set('Content-Type', 'application/json');
        } else {
            $response->setContent($this->getOfflineHtmlPage($request, '', 'upgrade-pending.html'));
            $response->headers->set('Content-Type', 'text/html');
        }

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function isHelpdeskOffline(Request $request = null)
    {
        if (isset($GLOBALS['DP_HELPDESK_DISABLED']) && $GLOBALS['DP_HELPDESK_DISABLED']) {
            return true;
        }

        // Offline setting applies to all but admin
        if ($this->getGlobalSetting('core.helpdesk_disabled')) {
            // exclude admin interface
            if ($this->interfaceInfo->isAdminInterface()) {
                return false;
            }

            // exclude agent login page (could be an admin needing to get back in)
            if ($request && strpos($request->getPathInfo(), '/agent/login') === 0) {
                return false;
            }

            // exclude legacy api for admin interface
            if ($request && $this->container->has('deskpro.api.request_auth')) {
                $apiUser = $this->container->get('deskpro.api.request_auth')->getApiUser();
                if ($apiUser && $apiUser->person && $apiUser->person->isAdmin()) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    private function isUpgradePending()
    {
        $build = $this->getGlobalSetting('core.deskpro_build');

        if (!$build) {
            // no build info: no db conn, not installed yet, etc
            return false;
        }

        // Make sure filesystem and db builds are the same, or else the upgrader needs to run
        if ($build < DP_BUILD_TIME) {
            return true;
        }

        return false;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getBrandSetting($name)
    {
        // this is overriden in HelpdeskOfflineSettingListener
        return;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getGlobalSetting($name)
    {
        return $this->container->get('settings_resolver')->getGlobalSettings()->get($name);
    }

    /**
     * @return string
     */
    private function getOfflineMessage()
    {
        $offline_message = null;
        if (file_exists($this->data_dir.'/helpdesk-offline-message.txt')) {
            $offline_message = file_get_contents($this->data_dir.'/helpdesk-offline-message.txt');
        } else {
            $offline_message = $this->getGlobalSetting('core.helpdesk_disabled_message');
        }

        if (!$offline_message) {
            $offline_message = 'The helpdesk is currently offline for maintenance. Please try again soon.';
        }

        return $offline_message;
    }

    /**
     * @param Request $request
     * @param string  $message
     * @param string  $tpl
     *
     * @return string
     */
    private function getOfflineHtmlPage(Request $request, $message, $tpl = 'helpdesk-disabled.html')
    {
        /* @var \DpRun\DpEnv */
        global $DP_ENV;
        $asset_url = $request->getUriForPath('/assets/'.$DP_ENV->getAppName().'/pub');

        $page_html = @file_get_contents(DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/Resources/views/kernel/'.$tpl) ?: '{{ CONTENT }}';
        $page_html = str_replace('{{ ASSET_URL }}', $asset_url, $page_html);
        $page_html = str_replace('{{ CONTENT }}', $message, $page_html);
        $page_html = LowTemplateHelper::injectAdminRedirect($request, $page_html);

        return $page_html;
    }
}
