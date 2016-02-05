<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Security\Authorization;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Metadata\ActionPermissionsMetadataFactory;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Metadata\MethodMetadata;
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
     * @var ActionPermissionsMetadataFactory
     */
    protected $factory;

    /**
     * @param ActionPermissionsMetadataFactory $factory
     */
    public function __construct(ActionPermissionsMetadataFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param string $method
     * @param mixed  $controller
     *
     * @return bool
     */
    protected function supports($method, $controller)
    {
        return $controller instanceof BaseController;
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
        $classMetadata  = $this->factory->getMetadataForClass(get_class($controller));
        $methodMetadata = $classMetadata->methodMetadata[$method];

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
        if ($token instanceof AnonymousToken) {
            return true;
        }

        $methodMetadata = $this->fetchMetadata($method, $controller);
        $mode           = $this->getMode($token->getName());

        return $this->checkMode($mode, $methodMetadata) && ($mode !== 'key' || $this->checkTags($methodMetadata));
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
     *
     * @return bool
     */
    protected function checkTags(MethodMetadata $methodMetadata)
    {
        return true;
    }

    /**
     * @param $token_name
     *
     * @return mixed
     */
    protected function getMode($token_name)
    {
        return $this->mode_map[$token_name];
    }
}
