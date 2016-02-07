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

namespace DeskPRO\Bundle\ApiBundle\EventListener;

use DeskPRO\Bundle\ApiBundle\Log\ApiLoggerInterface;
use DeskPRO\Bundle\AppBundle\Entity\ApiLog;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ApiLogListener.
 */
class ApiLogListener implements EventSubscriberInterface
{
    /**
     * @var ApiLoggerInterface
     */
    protected $logger;

    /**
     * @var TokenStorageInterface
     */
    protected $token_storage;

    /**
     * @param ApiLoggerInterface    $logger
     * @param TokenStorageInterface $token_storage
     */
    public function __construct(ApiLoggerInterface $logger, TokenStorageInterface $token_storage, EntityManager $em)
    {
        $this->logger        = $logger;
        $this->token_storage = $token_storage;
        $this->em            = $em;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array('onResponse', 1024),
        );
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
    {
        $token = $this->token_storage->getToken();
        if ($token && $token->getName() === 'api_key') {
            $request  = $event->getRequest();
            $response = $event->getResponse();
            /** @var \Application\DeskPRO\EntityRepository\ApiKey $key_repo */
            $key_repo = $this->em->getRepository('DeskPRO:ApiKey');

            if ($key = $key_repo->findByKeyString($this->token_storage->getToken()->getCredentials())) {
                /*
                 * @var \Application\DeskPRO\Entity\ApiKey $key
                 */
                $log = new ApiLog();
                $log
                    ->setStartTime(time())
                    ->setEndTime(time())
                    ->setKey($key)
                    ->setRequestedUri($request->getUri())
                    ->setResponseData($response->getContent())
                    ->setRequestData(var_export($request->request->all(), true))
                    ->setStatus($response->getStatusCode());
                $this->logger->log($log);
            }
        }
    }
}
