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

use \Application\DeskPRO\Entity;
use DeskPRO\Bundle\AppStoreBundle\Domain;
use DeskPRO\Bundle\AppStoreBundle\Domain\Constants;
use DeskPRO\Bundle\AppStoreBundle\Domain\StateScope;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure;
use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;


class AppStateParamConverter implements ParamConverterInterface
{
    /** @var Infrastructure\ApplicationStateDoctrineFinder */
    private $finder;

    /** @var Infrastructure\IdentifierParser */
    private $identifierParser;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        Infrastructure\ApplicationStateDoctrineFinder $finder
        , Infrastructure\IdentifierParser $identifierParser
        , TokenStorageInterface $tokenStorage
    ) {
        $this->finder           = $finder;
        $this->identifierParser = $identifierParser;
        $this->tokenStorage = $tokenStorage;
    }

    public function apply(Request $request, Configuration\ParamConverter $configuration)
    {
        $stateId = $this->parseStateId($request);
        if (is_null($stateId)) {
            return false;
        }

        if ($request->attributes->has('scope')) {
            $scope = $request->attributes->get('scope');
            $entity = $this->findStateWithScope($stateId, $scope);
        } else {
            $entity = $this->finder->find($stateId);
        }


        if ($entity) {
            $attributeName = $configuration->getName();
            $request->attributes->set($attributeName, $entity);
            return true;
        }

        return false;
    }

    /**
     * @param Request $request
     * @return Domain\ApplicationStateId|null
     */
    private function parseStateId(Request $request) {
        $appId     = $request->attributes->get('application');
        $stateName = $request->attributes->get('name');
        if (empty($appId) || empty($stateName)) {
            return null;
        }

        return new Domain\ApplicationStateId($appId, $stateName);
    }

    /**
     * @param Domain\ApplicationStateId $id
     * @param $scope
     * @return null|\DeskPRO\Bundle\AppBundle\Entity\AppStore\AppState
     */
    private function findStateWithScope(Domain\ApplicationStateId $id, $scope)
    {
        $existingScope = StateScope::parseString($scope);
        if (is_null($existingScope) || !StateScope::isValid($existingScope)) {
            return null;
        }

        $userId = $this->getOwnerId();
        if ($existingScope->getPermission() == Constants::STATE_PERMISSION_PRIVATE && is_null($userId)) {
            return null;
        }

        if ($userId) {
            return $this->finder->find($id, $userId);
        }

        return $this->finder->find($id);
    }

    /**
     * @return string|null
     */
    private function getOwnerId()
    {
        $token = $this->tokenStorage->getToken();
        if (! $token instanceof TokenInterface) {
            return null;
        }
// curios this is not set
//        if ($token->isAuthenticated()) {
//            return null;
//        }

        $user = $token->getUser();
        if (is_string($user)) {
            return $user;
        }

        if ($user instanceof Entity\Person) {
            return $user->getId();
        }

        if ($user instanceof UserInterface) {
            return $user->getUsername();
        }

        return null;
    }

    public function supports(Configuration\ParamConverter $configuration)
    {
        //TODO check that this class supports the configuration
        // todo temp excluded tag request until proper statement is added
        return $configuration->getClass() !== TagRequest::class;
    }
}
