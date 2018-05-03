<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\Portal;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Security\Voter\AbstractVoter;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Concerned only with wether or not a person can use a section / module of the portal.
 */
class UseSectionVoter extends AbstractVoter
{
    const USE_ARTICLES      = 'USE_ARTICLES';
    const USE_FEEDBACK      = 'USE_FEEDBACK';
    const USE_CHAT          = 'USE_CHAT';
    const USE_DOWNLOADS     = 'USE_DOWNLOADS';
    const USE_NEWS          = 'USE_NEWS';
    const USE_TICKETS       = 'USE_TICKETS';
    const USE_GUIDES        = 'USE_GUIDES';
    const VIEW_TICKETS_LINK = 'VIEW_TICKETS_LINK';

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [
            self::USE_ARTICLES,
            self::USE_FEEDBACK,
            self::USE_GUIDES,
            self::USE_CHAT,
            self::USE_DOWNLOADS,
            self::USE_NEWS,
            self::USE_TICKETS,
            self::VIEW_TICKETS_LINK,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $object, TokenInterface $token)
    {
        /** @var Person $user */
        $user = $token->getUser();

        if ($this->isLoggedIn($user)) {
            $permissionBag = $this->getPortalPermissionsManager()->getPermissionsBagForPerson($user);
        } else {
            $permissionBag = $this->getPortalPermissionsManager()->getPermissionsBagForGuest();
        }

        switch ($attribute) {
            case static::USE_ARTICLES:
                return $this->getActiveBrandSetting('core.apps_kb') && $permissionBag->get('articles.use');
            case static::USE_FEEDBACK:
                return $this->getActiveBrandSetting('core.apps_feedback') && $permissionBag->get('feedback.use');
            case static::USE_GUIDES:
                return $this->getActiveBrandSetting('core.apps_guides') && $permissionBag->get('guides.use');
            case static::USE_CHAT:
                // check global settings
                if (!$this->getActiveBrandSetting('core.apps_chat')
                    || !$permissionBag->get('chat.use')
                    || !$permissionBag->getAllowedChatDepartmentIds()) {
                    return false;
                }

                // check widget brand settings
                // get person user groups
                if (!$this->isLoggedIn($user)) {
                    $personUserGroupIds = [];
                } else {
                    $personUserGroupIds = $user->getUsergroupIds();
                }

                // get chat brand permission bag that intersects with user's user groups
                $activeBrand   = $this->container->get('brand_stack')->getActive()->getBrand();
                $brandSettings = $this->container->get('widget_settings_resolver')->getWidgetBrandOptions($activeBrand);

                if ($brandSettings->getChat()->getUserGroups() instanceof ArrayCollection) {
                    $brandUserGroups = $brandSettings->getChat()->getUserGroups()->toArray();
                } else {
                    $brandUserGroups = [];
                }

                $brandPermissionBag = $this->getPortalPermissionsManager()->getPartialPermissionBagForUsergroups(
                    array_values(array_intersect($personUserGroupIds, $brandUserGroups))
                );

                return $brandPermissionBag->get('chat.use');
            case static::USE_DOWNLOADS:
                return $this->getActiveBrandSetting('core.apps_downloads') && $permissionBag->get('downloads.use');
            case static::USE_NEWS:
                return $this->getActiveBrandSetting('core.apps_news') && $permissionBag->get('news.use');
            case static::USE_TICKETS:
                return $permissionBag->get('tickets.use');
            case static::VIEW_TICKETS_LINK:
                return $permissionBag->get('tickets.use')
                    || $this->isLoggedOutAndRegisteredUsergroupAllows($user, 'tickets.use');
        }

        return false;
    }

    /**
     * A very specific method that return true if:.
     *
     * 1. The user is logged out
     * 2. The given permission is granted in the "Registered" usergroup.
     *
     * @param $user
     * @param $perm
     *
     * @return bool
     */
    private function isLoggedOutAndRegisteredUsergroupAllows($user, $perm)
    {
        if ($this->isLoggedIn($user)) {
            return false; // logged in, so method this is false
        }

        $registered_bag = $this->getPortalPermissionsManager()->getPartialPermissionBagForRegisteredUsergroup();

        return $registered_bag->get($perm);
    }
}
