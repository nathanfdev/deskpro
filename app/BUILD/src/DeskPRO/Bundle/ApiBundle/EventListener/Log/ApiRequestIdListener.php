<?php

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
     * @var TokenStorageInterface
     */
    protected $token_storage;

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
            KernelEvents::REQUEST  => ['onRequest', 5], //should run after token was set
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        $this->helper->getRequestId($event->getRequest()->headers);
        $token = $this->token_storage->getToken();
        if ($token && $token instanceof AbstractApiSecurityToken) {
            $mode = ApiUtil::getMode($token->getName());
        } else {
            $mode = null;
        }
        $this->helper->setMode($mode);
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        $headers  = [LogHelper::REQUEST_ID_HEADER => $this->helper->getRequestId($event->getRequest()->headers)];
        $response->headers->add($headers);
    }
}
