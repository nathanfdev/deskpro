<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter;

use Application\DeskPRO\Entity\Person;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class AbstractVoter extends Voter
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * Iteratively check all given attributes by calling isGranted.
     *
     * This method terminates as soon as it is able to return ACCESS_GRANTED
     * If at least one attribute is supported, but access not granted, then ACCESS_DENIED is returned
     * Otherwise it will return ACCESS_ABSTAIN
     *
     * @param TokenInterface $token      A TokenInterface instance
     * @param object         $object     The object to secure
     * @param array          $attributes An array of attributes associated with the method being invoked
     *
     * @return int either ACCESS_GRANTED, ACCESS_ABSTAIN, or ACCESS_DENIED
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        // NOTE: we override the symfony default abstract voter to override this...
        //if (!$object || !$this->supportsClass(get_class($object))) {
        //    return self::ACCESS_ABSTAIN;
        //}

        // abstain vote by default in case none of the attributes are supported
        $vote = self::ACCESS_ABSTAIN;

        foreach ($attributes as $attribute) {
            if (!$this->supports($attribute, $object)) {
                continue;
            }

            // as soon as at least one attribute is supported, default is to deny access
            $vote = self::ACCESS_DENIED;

            if ($this->voteOnAttribute($attribute, $object, $token)) {
                // grant access as soon as at least one voter returns a positive response
                return self::ACCESS_GRANTED;
            }
        }

        return $vote;
    }

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
     *
     * @return bool
     */
    protected function isLoggedIn($user)
    {
        return $user instanceof Person && $user->getId() > 0;
    }

    /**
     * @param $user
     *
     * @return \DeskPRO\Bundle\AppBundle\Security\Permissions\PermissionsBag
     */
    public function getPermissionsBag($user)
    {
        if ($user instanceof Person) {
            return $this->getPortalPermissionsManager()->getPermissionsBagForPerson($user);
        }

        return $this->getPortalPermissionsManager()->getPermissionsBagForGuest();
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\Security\Permissions\Portal\PortalPermissionsManager
     */
    public function getPortalPermissionsManager()
    {
        return $this->container->get('portal_permissions_manager');
    }

    /**
     * @return \DeskPRO\Bundle\BrandBundle\Brand\BrandContainer
     */
    public function getActiveBrandContainer()
    {
        return $this->container->get('brand_stack')->getActive();
    }

    /**
     * @param $setting
     * @param mixed $default
     *
     * @return mixed
     */
    public function getActiveBrandSetting($setting, $default = null)
    {
        return $this->getActiveBrandContainer()->getSetting($setting, $default);
    }

    /**
     * @return \Symfony\Component\Security\Core\Authorization\AuthorizationChecker
     */
    public function getAuthorizationChecker()
    {
        return $this->container->get('security.authorization_checker');
    }
}
