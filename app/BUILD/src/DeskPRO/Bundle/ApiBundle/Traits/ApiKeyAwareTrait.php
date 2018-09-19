<?php

namespace DeskPRO\Bundle\ApiBundle\Traits;

use Application\DeskPRO\Entity\ApiKey;
use DeskPRO\Bundle\ApiBundle\Security\Token\ApiKeySecurityToken;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Trait ApiKeyAwareTrait.
 *
 * @property ContainerInterface $container
 */
trait ApiKeyAwareTrait
{
    /**
     * @return bool
     */
    protected function isAdminApiKeyRequest()
    {
        $token = $this->container->get('security.token_storage')->getToken();

        if (
            $token
            && $token instanceof ApiKeySecurityToken
            && $key = $this->container
                ->get('doctrine.orm.default_entity_manager')
                ->getRepository(ApiKey::class)
                ->findByKeyString($token->getCredentials())
        ) {
            /* @var $key ApiKey */
            return $key->isFlagSet(ApiKey::FLAG_SUPER_KEY);
        }

        return false;
    }
}
