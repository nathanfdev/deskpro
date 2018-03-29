<?php

namespace DeskPRO\Bundle\PortalBundle\HttpCache;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Usergroup;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Security\Permissions\Portal\PortalPermissionsManager;
use DeskPRO\Bundle\AppBundle\Security\Permissions\Portal\PortalUsergroupDecider;
use FOS\HttpCache\UserContext\ContextProviderInterface;
use FOS\HttpCache\UserContext\UserContext;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Class PortalUserHashContextProvider.
 */
class PortalUserHashContextProvider implements ContextProviderInterface
{
    /**
     * @var SettingsResolver
     */
    private $settingsResolver;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var PortalUsergroupDecider
     */
    private $usergroupDecider;

    /**
     * @var AuthorizationChecker
     */
    private $authChecker;

    /**
     * Constructor.
     *
     * @param SettingsResolver       $settingsResolver
     * @param TokenStorage           $tokenStorage
     * @param AuthorizationChecker   $authChecker
     * @param PortalUsergroupDecider $usergroupDecider
     */
    public function __construct(
        SettingsResolver       $settingsResolver,
        TokenStorage           $tokenStorage,
        AuthorizationChecker   $authChecker,
        PortalUsergroupDecider $usergroupDecider
    ) {
        $this->settingsResolver = $settingsResolver;
        $this->tokenStorage     = $tokenStorage;
        $this->usergroupDecider = $usergroupDecider;
        $this->authChecker      = $authChecker;
    }

    /**
     * This function is called before generating the hash of a UserContext.
     *
     * This allow to add a parameter on UserContext or set the whole array of parameters
     *
     * @param UserContext $context
     */
    public function updateUserContext(UserContext $context)
    {
        $cacheKey = 'guest';
        $token    = $this->tokenStorage->getToken();

        if ($token) {
            $person = $token->getUser();
            try {
                if ($person instanceof Person && $this->authChecker->isGranted('ROLE_USER')) {
                    // only if there is a valid, and authenticated, user in the security token
                    $settings   = $this->settingsResolver->getGlobalSettings();
                    $timestamp  = $settings->get(PortalPermissionsManager::CACHE_TIMESTAMP_SETTING_NAME);
                    $userGroups = Usergroup::generateUsergroupSetKey($this->usergroupDecider->getUsergroupIdsForPerson($person));

                    $cacheKey = "$timestamp-permissions-$userGroups";
                }
            } catch (AuthenticationException $e) {
                // keep the silence, nothing bad was happened, so we just keep 'guest' cache_key
            }
        }

        $context->addParameter('usergroup_cache_key', $cacheKey);
    }
}
