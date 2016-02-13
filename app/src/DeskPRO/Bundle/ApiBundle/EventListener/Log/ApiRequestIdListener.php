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

namespace DeskPRO\Bundle\ApiBundle\EventListener\Log;

use DeskPRO\Bundle\ApiBundle\Log\Helper\LogHelper;
use DeskPRO\Bundle\ApiBundle\Security\Token\AbstractApiSecurityToken;
use DeskPRO\Bundle\ApiBundle\Util\ApiUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ApiRequestIdListener.
 */
class ApiRequestIdListener implements EventSubscriberInterface
{
    /**
     * @var \DeskPRO\Bundle\ApiBundle\Log\Helper\LogHelper
     */
    protected $helper;

    /**
     * @param LogHelper             $helper
     * @param TokenStorageInterface $token_storage
     */
    public function __construct(LogHelper $helper, TokenStorageInterface $token_storage)
    {
        $this->helper        = $helper;
        $this->token_storage = $token_storage;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onResponse', 512], //make sure this stuff will be trigger before log and perhaps something else
            KernelEvents::REQUEST  => ['onRequest', 8], //should run after token was set
        ];
    }

    public function onRequest(GetResponseEvent $event)
    {
        $this->helper->getRequestId($event->getRequest()->headers);
        $token = $this->token_storage->getToken();
        if ($token && $token instanceof AbstractApiSecurityToken) {
            $mode = ApiUtil::getMode($token->getName());
        } else {
            $mode = ApiUtil::API_MODE_SESSION;
        }
        $this->helper->setMode($mode);
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        $response->headers->add([LogHelper::REQUEST_ID_HEADER => $this->helper->getRequestId()]);
    }
}
