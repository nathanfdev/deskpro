<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\EventListener;

use Application\DeskPRO\Entity\BanIp;
use DeskPRO\Bundle\AppBundle\Request\InterfaceInfo;
use DeskPRO\Bundle\AppBundle\Request\RequestUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Checks to see if the user IP is banned.
 */
final class UserBannedListener implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var InterfaceInfo
     */
    private $interfaceInfo;

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
            KernelEvents::REQUEST => ['onRequest'],
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        if (!(
            $event->isMasterRequest()
            && !RequestUtils::isLowRequest($event->getRequest())
            // dont run on admin, the admin might need to unblock themselves!
            && !$this->interfaceInfo->isAdminInterface()
        )
        ) {
            return;
        }

        $db              = $this->container->get('database_connection');
        $ip              = $event->getRequest()->getClientIp();
        $banIpRepository = $this->container->get('doctrine.orm.default_entity_manager')->getRepository(BanIp::class);
        $banned          = (bool) $banIpRepository->findOneBy(['is_range' => 0, 'banned_ip' => $ip]);

        if (!$banned) {
            $query  = $db->executeQuery('SELECT `banned_ip` FROM `ban_ips` WHERE `is_range` = 1');
            $ips    = $query->fetchAll(\PDO::FETCH_COLUMN);
            $banned = IpUtils::checkIp($ip, $ips);
        }

        if ($banned) {
            $page_html = file_get_contents(DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/Resources/views/kernel/banned.html');
            $page_html = str_replace('{{ ASSET_URL }}', $event->getRequest()->getBasePath().'/pub', $page_html);

            $response = new Response($page_html);
            $response->headers->set('X-DeskPRO-ErrorType', 'banned');

            $event->setResponse($response);
            $event->stopPropagation();
        }
    }
}
