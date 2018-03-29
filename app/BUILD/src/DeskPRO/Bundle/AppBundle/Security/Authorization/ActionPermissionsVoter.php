<?php

namespace DeskPRO\Bundle\AppBundle\Security\Authorization;

use Application\DeskPRO\Entity\ApiKey;
use Application\LegacyApiBundle\Controller\AbstractController;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Security\Token\AbstractApiSecurityToken;
use DeskPRO\Bundle\ApiBundle\Util\ApiUtil;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Metadata\MethodMetadata;
use DeskPRO\Bundle\AppBundle\Annotation\Metadata\MetadataFactory;
use Doctrine\ORM\EntityManager;
use Metadata\ClassMetadata;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class ActionPermissionsVoter.
 */
class ActionPermissionsVoter extends Voter
{
    /**
     * @var array
     */
    protected $mode_map = [
        'agent_session' => 'session',
        'api_key'       => 'key',
        'api_token'     => 'token',
    ];

    /**
     * @var \DeskPRO\Bundle\AppBundle\Annotation\Metadata\MetadataFactory
     */
    protected $factory;

    /**
     * @var ActionPermissionsHelper
     */
    protected $helper;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param \DeskPRO\Bundle\AppBundle\Annotation\Metadata\MetadataFactory $factory
     * @param ActionPermissionsHelper                                       $helper
     * @param EntityManager                                                 $em
     */
    public function __construct(
        MetadataFactory $factory,
        ActionPermissionsHelper $helper,
        EntityManager $em
    ) {
        $this->factory = $factory;
        $this->helper  = $helper;
        $this->em      = $em;
    }

    /**
     * @param string $method
     * @param mixed  $controller
     *
     * @return bool
     */
    protected function supports($method, $controller)
    {
        return $controller instanceof BaseController || $controller instanceof AbstractController;
    }

    /**
     * @param $method
     * @param $controller
     *
     * @throws \DeskPRO\Bundle\AppBundle\Annotation\Exception\AbstractClassException
     *
     * @return MethodMetadata
     */
    protected function fetchMetadata($method, $controller)
    {
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $this->factory->getMetadataForClass(get_class($controller));
        if (!isset($classMetadata->methodMetadata[$method])
            || !$methodMetadata = $classMetadata->methodMetadata[$method]
        ) {
            throw new \LogicException('Looks like you are requested action without "Action" suffix. Please contact developers"');
        }

        return $methodMetadata;
    }

    /**
     * @param string         $method
     * @param mixed          $controller
     * @param TokenInterface $token
     *
     * @throws \DeskPRO\Bundle\AppBundle\Annotation\Exception\AbstractClassException
     *
     * @return bool
     */
    protected function voteOnAttribute($method, $controller, TokenInterface $token)
    {
        if ($controller instanceof AbstractController) {
            $mode = $this->getOldMode($controller);
            $key  = $controller->apikey;
        } elseif ($token instanceof AnonymousToken) {
            return true;
        } else {
            /* @var AbstractApiSecurityToken $token */
            $mode = $this->getMode($token->getName());
            $key  = $this->getApiKeyByToken($token);
        }

        $methodMetadata = $this->fetchMetadata($method, $controller);

        return $this->checkMode($mode, $methodMetadata)
                && ($mode !== 'key' || $this->checkTags($methodMetadata, $key));
    }

    /**
     * @param AbstractController $controller
     *
     * @return string
     */
    protected function getOldMode(AbstractController $controller)
    {
        return $controller->apikey ? 'key' : 'session';
    }

    /**
     * @param $mode
     * @param MethodMetadata $methodMetadata
     *
     * @return bool
     */
    protected function checkMode($mode, MethodMetadata $methodMetadata)
    {
        return in_array($mode, (array) $methodMetadata->getModes());
    }

    /**
     * @param MethodMetadata $methodMetadata
     * @param ApiKey         $key
     *
     * @return bool
     */
    protected function checkTags(MethodMetadata $methodMetadata, ApiKey $key)
    {
        $action_tags   = $methodMetadata->getTags();
        $gathered_tags = [];
        foreach ($key->getActions() as $action) {
            $gathered_tags[] = $action->getAction();
        }

        return $this->helper->calculateAccess($action_tags, $gathered_tags);
    }

    /**
     * @param TokenInterface $token
     *
     * @return \Application\DeskPRO\EntityRepository\ApiKey
     */
    protected function getApiKeyByToken(TokenInterface $token)
    {
        /** @var \Application\DeskPRO\EntityRepository\ApiKey $key_repo */
        $key_repo = $this->em->getRepository('DeskPRO:ApiKey');
        $key      = $key_repo->findByKeyString($token->getCredentials());

        return $key;
    }

    /**
     * @param $token_name
     *
     * @return mixed
     */
    protected function getMode($token_name)
    {
        return ApiUtil::getMode($token_name);
    }
}
