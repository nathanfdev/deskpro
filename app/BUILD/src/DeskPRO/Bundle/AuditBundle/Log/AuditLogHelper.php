<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AuditBundle\Log;

use Application\DeskPRO\Entity\ApiKey;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\ApiKey as ApiKeyRepository;
use Application\DeskPRO\HttpFoundation\Session;
use DeskPRO\Bundle\ApiBundle\Security\Token\AbstractApiSecurityToken;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class AuditLogHelper
 * This class mostly used by controllers to simplify some audit actions.
 */
class AuditLogHelper
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * AuditLogHelper constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return Performer
     */
    public function getPerformer()
    {
        $performer = new Performer('unknown');

        if (
            $this->tokenStorage()
            && $this->tokenStorage()->getToken()
            && ($user = $this->tokenStorage()->getToken()->getUser())
        ) {
            if ($user instanceof Person) {
                /* @var Person $user */
                $performer->setName($user->getDisplayName())->setId($user->getId());
            } elseif (is_scalar($user)) {
                $legacyUser = null;

                // Next two if-blocks are all about legacy handling, and the last one just partial
                if (($session = $this->container->get('session')) && $session instanceof Session) {
                    $legacyUser = $session->getPerson();
                }

                if (!$legacyUser instanceof Person && $this->container->has('deskpro.api.request_auth')) {
                    /** @var \Application\LegacyApiBundle\Request\RequestAuth $request_auth */
                    $request_auth = $this->container->get('deskpro.api.request_auth');
                    $legacyUser   = $request_auth->getApiUser()->person;
                }

                if ($legacyUser instanceof Person) {
                    $performer->setName($legacyUser->getDisplayName())->setId($legacyUser->getId());
                } elseif ($user) {
                    $performer->setName((string) $user);
                }
            }
        } else {
            return $performer->setName('System');
        }

        return $performer;
    }

    /**
     * @return AuditLog
     */
    public function createAuditLog()
    {
        $log       = new AuditLog();
        $performer = $this->getPerformer();
        $log->setPerformerId($performer->getId())->setPerformerName($performer->getName());
        $this->setApiKey($log);

        return $log;
    }

    /**
     * @param AuditLog $log
     */
    private function setApiKey(AuditLog $log)
    {
        $token = $this->tokenStorage()->getToken();

        if ($token instanceof AbstractApiSecurityToken && $token->getName() === 'api_key') {
            $key = $this->apiKeyRepo()->findByKeyString($token->getCredentials());
            if ($key) {
                $log->setApiKey($key->getId());
            }
        }
    }

    /**
     * @return TokenStorageInterface
     */
    private function tokenStorage()
    {
        return $this->container->get('security.token_storage');
    }

    /**
     * @return ApiKeyRepository
     */
    private function apiKeyRepo()
    {
        return $this->container->get('doctrine.orm.default_entity_manager')->getRepository(ApiKey::class);
    }
}
