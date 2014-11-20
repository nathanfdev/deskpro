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

abstract class AbstractVoter implements VoterInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * We can't inject the security context directly because we are inside of it. Avoiding circular dependency here.
     *
     * Further, we can fetch anything out lazily (since Symfony will instantaite all voters as soon as we use authorization
     * it will actually build the entire dependency tree if we use "normal" DI.)
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
     * @return \Symfony\Component\Security\Core\SecurityContext
     */
    public function getSecurityContext()
    {
        return $this->container->get('security.context');
    }

    /**
     * @return \Application\AuthBundle\Permissions\Portal\PortalPermissionsManager
     */
    public function getPortalPermissionsManager()
    {
        return $this->container->get('portal_permissions_manager');
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return true === $this->getSupportedAttributes() || in_array($attribute, $this->getSupportedAttributes());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        if($this->getSupportedClasses() === true) {
            return true;
        }

        foreach ($this->getSupportedClasses() as $supportedClass) {
            if ($supportedClass === $class || is_subclass_of($class, $supportedClass)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Iteratively check all given attributes by calling isGranted
     *
     * This method terminates as soon as it is able to return ACCESS_GRANTED
     * If at least one attribute is supported, but access not granted, then ACCESS_DENIED is returned
     * Otherwise it will return ACCESS_ABSTAIN
     *
     * @param TokenInterface $token      A TokenInterface instance
     * @param object         $object     The object to secure
     * @param array          $attributes An array of attributes associated with the method being invoked
     *
     * @return int     either ACCESS_GRANTED, ACCESS_ABSTAIN, or ACCESS_DENIED
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!$this->supportsClass(get_class($object))) {
            return self::ACCESS_ABSTAIN;
        }
        // abstain vote by default in case none of the attributes are supported
        $vote = self::ACCESS_ABSTAIN;
        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }
            // as soon as at least one attribute is supported, default is to deny access
            $vote = self::ACCESS_DENIED;
            if ($this->isGranted($attribute, $object, $token->getUser())) {
                // grant access as soon as at least one voter returns a positive response
                return self::ACCESS_GRANTED;
            }
        }

        return $vote;
    }

    /**
     * Return an array of supported classes. This will be called by supportsClass
     *
     * @return array    an array of supported classes, i.e. array('Acme\DemoBundle\Model\Product')
     */
    abstract protected function getSupportedClasses();

    /**
     * Return an array of supported attributes. This will be called by supportsAttribute
     *
     * @return array    an array of supported attributes, i.e. array('CREATE', 'READ')
     */
    abstract protected function getSupportedAttributes();

    /**
     * Perform a single access check operation on a given attribute, object and (optionally) user
     * It is safe to assume that $attribute and $object's class pass supportsAttribute/supportsClass
     * $user can be one of the following:
     *   a UserInterface object (fully authenticated user)
     *   a string               (anonymously authenticated user)
     *
     * @param string               $attribute
     * @param object               $object
     * @param UserInterface|string $user
     *
     * @return bool
     */
    abstract protected function isGranted($attribute, $object, $user = null);

    /**
     * @param $user
     * @return bool
     */
    protected function isLoggedIn($user)
    {
        return $user instanceof Person && $user->id > 0;
    }
}