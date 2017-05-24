<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppStoreBundle\ParamConverter;

use DeskPRO\Bundle\AppStoreBundle\Domain;
use DeskPRO\Bundle\AppStoreBundle\Domain\Constants;
use DeskPRO\Bundle\AppStoreBundle\Domain\StateScope;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure;
use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;


class AppStateParamConverter implements ParamConverterInterface
{
    /** @var Infrastructure\ApplicationStateDoctrineFinder */
    private $finder;

    /** @var Infrastructure\IdentifierParser */
    private $identifierParser;

    /** @var TokenStorage */
    private $tokenStorage;

    public function __construct(Infrastructure\ApplicationStateDoctrineFinder $finder, Infrastructure\IdentifierParser $identifierParser, TokenStorage $tokenStorage)
    {
        $this->finder           = $finder;
        $this->identifierParser = $identifierParser;
        $this->tokenStorage = $tokenStorage;
    }

    public function apply(Request $request, Configuration\ParamConverter $configuration)
    {
        $appId     = $request->attributes->get('application');
        $stateName = $request->attributes->get('name');
        $scope = $request->attributes->get('scope');

        $existingScope = StateScope::parseString($scope);
        if (is_null($existingScope) || !StateScope::isValid($existingScope)) {
            return false;
        }

        $userId = $this->getAuthUserId();
        if ($existingScope->getPermission() == Constants::STATE_PERMISSION_PRIVATE && is_null($userId)) {
            return false;
        }

        $stateKey = new Domain\ApplicationStateId($appId, $stateName);
        $entity   = $userId ? $this->finder->find($stateKey, $userId) : $this->finder->find($stateKey);

        if ($entity) {
            $attributeName = $configuration->getName();
            $request->attributes->set($attributeName, $entity);
        }

        return true;
    }

    /**
     * @return string|null
     */
    public function getAuthUserId()
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return;
        }

        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return;
        }

        return $user->getId();
    }

    public function supports(Configuration\ParamConverter $configuration)
    {
        //TODO check that this class supports the configuration
        // todo temp excluded tag request until proper statement is added
        return $configuration->getClass() !== TagRequest::class;
    }
}
