<?php

namespace DeskPRO\Bundle\AuditBundle\Log;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\ApiKey;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\ApiKey as ApiKeyRepository;
use DeskPRO\Bundle\ApiBundle\Security\Token\AbstractApiSecurityToken;
use Doctrine\Common\Util\ClassUtils;
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
        $this->container         = $container;
        $this->supportedEntities = [];
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
                $legacyUser = App::getCurrentPerson();

                if ($legacyUser instanceof Person) {
                    $performer->setName($legacyUser->getDisplayName())->setId($legacyUser->getId());
                } elseif ($user) {
                    $performer->setName((string) $user);
                }
            }
        } else {
            $performer->setName('System');
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

    public function supportedEntity($entity)
    {
        if (!$this->supportedEntities) {
            $settingsBag             = $this->container->get('settings_resolver')->getGlobalSettings(false);
            $rawConfig               = $settingsBag->get('audit_log.configuration');
            $this->supportedEntities = array_keys($rawConfig);
        }
        $class = ClassUtils::getRealClass(get_class($entity));

        return in_array($class, $this->supportedEntities);
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
