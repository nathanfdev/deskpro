<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

namespace Application\AuthBundle\Voter;

use Application\DeskPRO\Entity\Person;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

abstract class AbstractVoter extends \Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * We can't inject the authorization checker directly because we are inside of it. Avoiding circular dependency here.
     *
     * Further, we can fetch anything out lazily (and this is important because Symfony will instantaite all voters at
     * once and actually build the entire dependency tree if we use "normal" DI)
     *
     * Just use the container inside of voters :)
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param $user
     * @return bool
     */
    protected function isLoggedIn($user)
    {
        return $user instanceof Person && $user->id > 0;
    }

    /**
     * @return \Application\AuthBundle\Permissions\Portal\PortalPermissionsManager
     */
    public function getPortalPermissionsManager()
    {
        return $this->container->get('portal_permissions_manager');
    }

    /**
     * @return \Symfony\Component\Security\Core\Authorization\AuthorizationChecker
     */
    public function getAuthorizationChecker()
    {
        return $this->container->get('security.authorization_checker');
    }
}