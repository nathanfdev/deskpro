<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\EventListener;

use Application\DeskPRO\HttpFoundation\Session as AgentSession;
use DeskPRO\Bundle\AppBundle\EventListener\Helper\LowTemplateHelper;
use DeskPRO\Bundle\AppBundle\Request\InterfaceInfo;
use DeskPRO\Bundle\AppBundle\Request\RequestUtils;
use DpSys\License;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * This performs lic checks.
 *
 * NOTE: In the distro, this file is moved into the encrypted system.php file.
 * This file will become an empty placeholder in the distro.
 */
final class LicenseCheckListener implements EventSubscriberInterface
{
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
    private $assetUrl;

    public function __construct(ContainerInterface $container, InterfaceInfo $interfaceInfo)
    {
        $this->container     = $container;
        $this->interfaceInfo = $interfaceInfo;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onResponse', -100],
        ];
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
    {
        // - We run through license errors on user/agent interface (admin has warning at top)
        // - And only when its an HTML page of course (e.g., cant render an HTML page when the client expects json)
        // - And we dont show on login pages, because we might be mid-login to admin interface now
        // - And we dont care about error pages
        if (!(
            $event->isMasterRequest()
            && !RequestUtils::isLowRequest($event->getRequest())
            && $event->getRequest()->getMethod() === 'GET'
            && !$event->getRequest()->isXmlHttpRequest()
            && $this->interfaceInfo->isInterfaceId([InterfaceInfo::ID_USER, InterfaceInfo::ID_AGENT])
            && in_array('text/html', $event->getRequest()->getAcceptableContentTypes())
            && strpos($event->getRequest()->getPathInfo(), '/login') === false
            && $event->getResponse()->isSuccessful()
        )) {
            return;
        }

        /* @var \DpRun\DpEnv */
        global $DP_ENV;
        $this->assetUrl = $event->getRequest()->getUriForPath('/assets/'.$DP_ENV->getAppName().'/pub');

        if (defined('DPC_IS_CLOUD')) {
            $this->doCloudChecks($event);
        } else {
            $this->doOnsiteChecks($event);
        }
    }

    /**
     * Performs checks for an on-site license.
     *
     * @param FilterResponseEvent $event
     */
    private function doOnsiteChecks(FilterResponseEvent $event)
    {
        $lic     = License::getLicense();
        $request = $event->getRequest();

        // No license at all
        if (!$lic->hasLicense()) {
            $settings = $this->container->get('settings_resolver');
            if (!$settings->getGlobalSettings()->get('core.setup_initial')) {
                $event->setResponse(new RedirectResponse($request->getBaseUrl().'/admin/start'));
                $event->stopPropagation();

                return;
            }

            $event->setResponse($this->getLicErrorPageResponse($request, 'You have not entered a license code. Go to /admin and enter your license code now.'));
            $event->stopPropagation();

            return;
        }

        // Check number of agents
        if ($this->interfaceInfo->isAgentInterface() && $lic->getMaxAgents()) {
            $db    = $this->container->get('database_connection');
            $count = $db->fetchColumn('SELECT COUNT(*) FROM people WHERE is_agent = 1 AND is_deleted = 0');

            if ($count > $lic->getMaxAgents()) {
                $event->setResponse($this->getLicErrorPageResponse($request, "
                    Your helpdesk is using more agents than your license allows.<br/><br/>
                    - Number of agents: {$count}<br/><br/>
                    - Number of seats available: {$lic->getMaxAgents()}<br/><br/>
                    Please contact an administrator to correct the problem.
                "));
                $event->stopPropagation();

                return;
            }
        }

        // Check expiry
        // Agent: Lic error immediately
        // User: Lic error after 14 days (unless demo, then immediately)
        if ($lic->isPastExpireDate() && (
            $lic->isDemo()
            || $this->interfaceInfo->isAgentInterface()
            || ($this->interfaceInfo->isUserInterface() && $lic->isPastExpireDate() >= 14)
        )) {
            $date    = $lic->getExpireDate()->format('F jS');
            $message = "Your helpdesk license expired on {$date}. To continue using your helpdesk,
                an administrator needs to renew the license. Go to
                <a href='{$this->container->get('router')->generate('admin_interface')}#/license'>billing settings</a>.";

            // In agent interface add note if they are not an admin
            if ($this->container->initialized('session')
                && $this->container->get('session') instanceof AgentSession
                && $this->container->get('session')->getPerson()
                && !$this->container->get('session')->getPerson()->canAdmin()
            ) {
                $message .= '<p>Note: You are not an administrator and cannot make this change yourself.
                    Please get your administrator to log in and update the license.</p>';
            }

            $event->setResponse($this->getLicErrorPageResponse($request, $message));
            $event->stopPropagation();

            return;
        }
    }

    /**
     * Performs checks for a cloud license.
     *
     * @param FilterResponseEvent $event
     */
    private function doCloudChecks(FilterResponseEvent $event)
    {
        $lic     = License::getLicense();
        $request = $event->getRequest();

        // Expired demos
        if ($lic->isDemo() && $lic->isPastExpireDate()) {
            $event->setResponse(new RedirectResponse($event->getRequest()->getUriForPath('/cloud/expired_demo')));
            $event->stopPropagation();

            return;
        }

        // Failed billing
        if ($lic->isPastExpireDate()) {
            if (
                ($this->interfaceInfo->isAgentInterface() && defined('DPC_AGENT_OFF') && DPC_AGENT_OFF)
                || ($this->interfaceInfo->isUserInterface() && defined('DPC_USER_OFF') && DPC_USER_OFF)
            ) {
                $event->setResponse($this->getCloudErrorPageResponse($request, 'cloud-error.bill-failed.html'));
                $event->stopPropagation();

                return;
            }
        }

        // The site might be turned off manually
        if (
            ($this->interfaceInfo->isAdminInterface() && defined('DPC_ADMIN_OFF') && DPC_ADMIN_OFF)
            || ($this->interfaceInfo->isAgentInterface() && defined('DPC_AGENT_OFF') && DPC_AGENT_OFF)
            || ($this->interfaceInfo->isUserInterface() && defined('DPC_USER_OFF') && DPC_USER_OFF)
        ) {
            $event->setResponse($this->getCloudErrorPageResponse($request, 'cloud-error.offline.html', defined('DPC_OFF_REASON') ? DPC_OFF_REASON : ''));
            $event->stopPropagation();

            return;
        }

        // The whole site might be offline
        if (defined('DPC_SYS_DISABLED') && DPC_SYS_DISABLED) {
            $event->setResponse($this->getCloudErrorPageResponse($request, 'cloud-error.offline.html', ''));
            $event->stopPropagation();

            return;
        }
    }

    /**
     * @param Request $request
     * @param string  $message
     *
     * @return Response
     */
    private function getLicErrorPageResponse(Request $request, $message)
    {
        $response = new Response($this->getLicErrorPage($request, $message));
        $response->headers->set('X-DeskPRO-ErrorType', 'license');

        return $response;
    }

    /**
     * @param Request $request
     * @param string  $message
     *
     * @return string
     */
    private function getLicErrorPage(Request $request, $message)
    {
        $asset_url = $this->assetUrl;
        $tpl       = 'lic-error.html';

        $page_html = @file_get_contents(DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/Resources/views/kernel/'.$tpl) ?: '{{ CONTENT }}';
        $page_html = str_replace('{{ ASSET_URL }}', $asset_url, $page_html);
        $page_html = str_replace('{{ CONTENT }}', $message, $page_html);
        $page_html = LowTemplateHelper::injectAdminRedirect($request, $page_html);

        return $page_html;
    }

    /**
     * @param Request $request
     * @param string  $tpl
     * @param string  $message
     *
     * @return Response
     */
    private function getCloudErrorPageResponse(Request $request, $tpl, $message = '')
    {
        $response = new Response($this->getCloudErrorPage($request, $tpl, $message));
        $response->headers->set('X-DeskPRO-ErrorType', 'license');

        return $response;
    }

    /**
     * @param Request $request
     * @param string  $tpl
     * @param string  $message
     *
     * @return string
     */
    private function getCloudErrorPage(Request $request, $tpl, $message = '')
    {
        $asset_url = $this->assetUrl;

        $page_html = @file_get_contents(DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/Resources/views/kernel/'.$tpl) ?: '{{ CONTENT }}';
        $page_html = str_replace('{{ ASSET_URL }}', $asset_url, $page_html);
        $page_html = str_replace('{{ CONTENT }}', $message, $page_html);
        $page_html = LowTemplateHelper::injectAdminRedirect($request, $page_html);

        return $page_html;
    }
}
