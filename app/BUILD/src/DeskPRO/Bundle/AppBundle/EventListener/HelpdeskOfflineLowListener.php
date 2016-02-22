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
 */
namespace DeskPRO\Bundle\AppBundle\EventListener;

use Symfony\Component\HttpKernel\KernelEvents;
use DeskPRO\Bundle\AppBundle\Request\InterfaceInfo;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Check if the helpdesk is offline.
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
     * @var string
     */
    private $data_dir;

    public function __construct(InterfaceInfo $interfaceInfo, $data_dir)
    {
        $this->interfaceInfo = $interfaceInfo;
        $this->data_dir      = $data_dir;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onPreRequest', 2000), // runs before everything
        );
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onPreRequest(GetResponseEvent $event)
    {
        if ($this->isHelpdeskOffline()) {
            $event->setResponse($this->createOfflineResponse($event->getRequest()));
            $event->stopPropagation();
        } elseif ($this->isUpgradePending()) {
            $event->setResponse($this->createUpgradePendingResponse($event->getRequest()));
            $event->stopPropagation();
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
            $response->setContent($this->getOfflineHtmlPage($message, $request->getBasePath().'/pub'));
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
            $message = 'An upgrade is pending. An administrator must run the dp:upgrade command.';
            $response->setContent(json_encode([
                'type'         => 'upgrade_pending',
                'message_html' => $message,
                'message_text' => $message,
            ]));
            $response->headers->set('Content-Type', 'application/json');
        } else {
            $response->setContent($this->getOfflineHtmlPage('', $request->getBasePath().'/pub', 'upgrade-pending.html'));
            $response->headers->set('Content-Type', 'text/html');
        }

        return $response;
    }

    /**
     * @return bool
     */
    private function isHelpdeskOffline()
    {
        if (isset($GLOBALS['DP_HELPDESK_DISABLED']) && $GLOBALS['DP_HELPDESK_DISABLED']) {
            return true;
        }

        // Offline file is inserted on cmdline upgrade,
        // we want to disable all access
        if (is_file($this->data_dir.'/helpdesk-offline.trigger')) {
            return true;
        }

        // Offline setting applies to all but admin
        if ($this->getBrandSetting('core.helpdesk_disabled') && !$this->interfaceInfo->isAdminInterface()) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    private function isUpgradePending()
    {
        $build = $this->getBrandSetting('core.deskpro_build');

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
     * @return string
     */
    private function getOfflineMessage()
    {
        $offline_message = null;
        if (file_exists($this->data_dir.'/helpdesk-offline-message.txt')) {
            $offline_message = file_get_contents($this->data_dir.'/helpdesk-offline-message.txt');
        } else {
            $offline_message = $this->getBrandSetting('core.helpdesk_disabled_message');
        }

        if (!$offline_message) {
            $offline_message = 'The helpdesk is currently offline for maintenance. Please try again soon.';
        }

        return $offline_message;
    }

    /**
     * @param string $message
     *
     * @return string
     */
    private function getOfflineHtmlPage($message, $asset_url, $tpl = 'helpdesk-disabled.html')
    {
        $page_html = @file_get_contents(DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/Resources/views/kernel/'.$tpl) ?: '{{ CONTENT }}';
        $page_html = str_replace('{{ ASSET_URL }}', $asset_url, $page_html);
        $page_html = str_replace('{{ CONTENT }}', $message, $page_html);

        return $page_html;
    }
}
