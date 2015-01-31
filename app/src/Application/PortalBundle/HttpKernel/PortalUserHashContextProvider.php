<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\PortalBundle\HttpKernel;

use Application\AuthBundle\Permissions\Portal\PortalPermissionsManager;
use Application\DeskPRO\Entity\Person;
use FOS\HttpCache\UserContext\ContextProviderInterface;
use FOS\HttpCache\UserContext\UserContext;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class PortalUserHashContextProvider implements ContextProviderInterface
{
    /**
     * @var TokenStorage
     */
    private $token_storage;

    /**
     * @var PortalPermissionsManager
     */
    private $permissions_manager;

    /**
     * @var AuthorizationChecker
     */
    private $auth_checker;

    public function __construct(TokenStorage $token_storage, AuthorizationChecker $auth_checker, PortalPermissionsManager $permissions_manager)
    {
        $this->token_storage = $token_storage;
        $this->permissions_manager = $permissions_manager;
        $this->auth_checker = $auth_checker;
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
        $cache_key = null;
        if ($token = $this->token_storage->getToken()) {
            $person = $token->getUser();
            if ($person instanceof Person && $this->auth_checker->isGranted('ROLE_USER')) {
                // only if there is a valid, and authenticated, user in the security token
                $cache_key = $this->permissions_manager->getCacheKeyForPerson($person);
            }
        }

        if (!$cache_key) {
            // not a ROLE_USER, or no Person is token
            // NOTE: this will get HASHED to the x-user-context-hash (to a value of PortalHttpCache::GUEST_HASH)
            // see PortalHttpCache. Never check for this in a header. Use portal_cache_helper->isGuest() instead.
            $cache_key = 'guest';
        }

        $context->addParameter('usergroup_cache_key', $cache_key);
    }
}
