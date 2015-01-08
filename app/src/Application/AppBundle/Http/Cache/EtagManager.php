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

namespace Application\AppBundle\Http\Cache;

use Application\AuthBundle\Permissions\Portal\PortalUsergroupDecider;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Application\DeskPRO\Entity\Person;

/**
 * A central service that helps generate the proper etag for a page.
 */
class EtagManager 
{
    /**
     * @var AuthorizationChecker
     */
    private $auth_checker;

    /**
     * @var TokenStorage
     */
    private $token_storage;

    /**
     * @var PortalUsergroupDecider
     */
    private $portal_usergroup_decider;

    public function __construct(AuthorizationChecker $auth_checker, TokenStorage $token_storage, PortalUsergroupDecider $portal_usergroup_decider)
    {
        $this->auth_checker = $auth_checker;
        $this->token_storage = $token_storage;
        $this->portal_usergroup_decider = $portal_usergroup_decider;
    }

    public function getEtagForCurrentUser()
    {
        if ($this->auth_checker->isGranted('ROLE_USER')) {
            return $this->generateByPersonUsergroups($this->token_storage->getToken()->getUser());
        }

        return $this->generateForGuest();
    }

    public function generateForGuest()
    {
        return 'guest';
    }

    public function generateByPersonUsergroups(Person $person)
    {
        $usergroup_ids = $this->portal_usergroup_decider->getUsergroupIdsForPerson($person);

        return 'usergroups' . implode('-', $usergroup_ids);
    }
}
