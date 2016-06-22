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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\HttpCache;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Security\Permissions\Portal\PortalPermissionsManager;
use FOS\HttpCache\UserContext\ContextProviderInterface;
use FOS\HttpCache\UserContext\UserContext;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class PortalUserHashContextProvider implements ContextProviderInterface
{
    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var PortalPermissionsManager
     */
    private $permissionsManager;

    /**
     * @var AuthorizationChecker
     */
    private $authChecker;

    public function __construct(
        TokenStorage $tokenStorage,
        AuthorizationChecker $authChecker,
        PortalPermissionsManager $permissionsManager
    ) {
        $this->tokenStorage       = $tokenStorage;
        $this->permissionsManager = $permissionsManager;
        $this->authChecker        = $authChecker;
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
        if ($token = $this->tokenStorage->getToken()) {
            $person = $token->getUser();
            try {
                if ($person instanceof Person && $this->authChecker->isGranted('ROLE_USER')) {
                    // only if there is a valid, and authenticated, user in the security token
                    $cacheKey = $this->permissionsManager->getCacheKeyForPerson($person);
                }
            } catch (BadCredentialsException $e) {
                // keep the silence, nothing bad was happened, so we just keep 'guest' cache_key
            }
        }

        $context->addParameter('usergroup_cache_key', $cacheKey);
    }
}
