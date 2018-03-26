<?php

namespace DpBehat\Data;

use DpBehat\BaseContext;

/**
 * Class OrganizationContext.
 */
class OrganizationContext extends BaseContext
{
    /**
     * @Given the :user is in :organization organization
     *
     * @param $user
     * @param $organization
     */
    public function addUserToOrganization($user, $organization)
    {
        $user               = DataContext::getReference($user);
        $organization       = DataContext::getReference($organization);
        $user->organization = $organization;
        $this->em()->persist($user);
        $this->em()->flush();
    }

    /**
     * @Given the :user has no organization
     *
     * @param $user
     */
    public function removeUserOrganization($user)
    {
        $user               = DataContext::getReference($user);
        $user->organization = null;
        $this->em()->persist($user);
        $this->em()->flush();
    }
}
