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
        $banned          = (bool) $banIpRepository->findOneBy(['banned_ip' => $ip]);

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
