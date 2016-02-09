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

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\ApiBundle\Log\ApiLoggerInterface;
use DeskPRO\Bundle\ApiBundle\Security\Token\AbstractApiSecurityToken;
use DeskPRO\Bundle\AppBundle\Entity\ApiLog;
use DeskPRO\Bundle\AppBundle\HttpKernel\ResponseUtil;
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
     * @var bool
     */
    protected $enabled;

    /**
     * @param ApiLoggerInterface    $logger
     * @param TokenStorageInterface $token_storage
     * @param EntityManager         $em
     * @param SettingsResolver      $resolver
     */
    public function __construct(
        ApiLoggerInterface $logger,
        TokenStorageInterface $token_storage,
        EntityManager $em,
        SettingsResolver $resolver
    ) {
        $this->enabled       = $resolver->getGlobalSettings()->get('api_logger.enabled');
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
            KernelEvents::RESPONSE => array('onResponse', 32),
        );
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
    {
        if ($this->enabled && $event->isMasterRequest()) {
            $token = $this->token_storage->getToken();
            if ($token instanceof AbstractApiSecurityToken && $token->getName() === 'api_key') {
                $request  = $event->getRequest();
                $response = $event->getResponse();
                /** @var \Application\DeskPRO\EntityRepository\ApiKey $key_repo */
                $key_repo = $this->em->getRepository('DeskPRO:ApiKey');

                if ($key = $key_repo->findByKeyString($this->token_storage->getToken()->getCredentials())) {

                    /*
                     * @var \Application\DeskPRO\Entity\ApiKey $key
                     */
                    $log = new ApiLog();

                    $response_data = [
                        'headers' => $response->headers->all(),
                        'body'    => $response->getContent(),
                    ];

                    $request_data = [
                        'headers'    => $request->headers->all(),
                        'body'       => $request->getContent(),
                        'query'      => $request->query->all(),
                        'post'       => $request->request->all(),
                        'files'      => $request->files->all(),
                        'server'     => $request->server->all(),
                        'attributes' => $request->attributes->all(),
                    ];

                    $log
                        ->setRequestId(ResponseUtil::getRequestIdFromResponse($response))
                        ->setStartTime((int) DP_START_TIME)
                        ->setEndTime(time())
                        ->setKey($key)
                        ->setRequestedUri($request->getUri())
                        ->setResponseData($response_data)
                        ->setRequestData($request_data)
                        ->setStatus($response->getStatusCode());
                    $this->logger->log($log);
                }
            }
        }
    }
}
