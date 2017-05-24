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

namespace DeskPRO\Bundle\ApiBundle\Controller\Apps;

use DeskPRO\Bundle\AppStoreBundle\API\AppStateRepresentation;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Entity;
use DeskPRO\Bundle\AppStoreBundle;
use DeskPRO\Bundle\AppStoreBundle\Domain\StateScope;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation as DeskproAnnotations;

/**
 * Class AppsController
 *
 * @DeskproAnnotations\ApiModes("standard")
 * @Rest\Route("/apps/{application}/state")
 */
class AppStateController extends BaseController
{
    /**
     * @Rest\Get("")
     * @DeskproAnnotations\ApiUserContext("agent")
     *
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\AppInstance", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppInstanceParamConverter")
     * @ParamConverter("stateFilter", class="DeskPRO\Bundle\AppStoreBundle\Domain\SearchStateFilter", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\StateFilterParamConverter")
     *
     * @param Entity\AppStore\AppInstance $application
     * @param AppStoreBundle\Domain\SearchStateFilter $stateFilter
     * @return AppStoreBundle\Domain\ApplicationState[]
     */
    public function listStateAction(Entity\AppStore\AppInstance $application, AppStoreBundle\Domain\SearchStateFilter $stateFilter)
    {
        //TODO filter by current user
        /** @var AppStoreBundle\Domain\ApplicationStateFinder $applicationStateFinder */
        $applicationStateFinder = $this->container->get(AppStoreBundle\Domain\ApplicationStateFinder::class);
        $list = $applicationStateFinder->findApplicationStateByFilter($application, $stateFilter);

        $converter = function(AppStoreBundle\Domain\ApplicationState $state) {
            return [
                'app_id' => $state->getInstanceId(),
                'name' => $state->getName(),
                'scope' => $state->getScope(),
                'value' => json_decode($state->getValue(), $assoc=true)
            ];
        };

        return array_map($converter, $list);
    }

    /**
     * @Rest\Head("/{name}/{scope}")
     * @DeskproAnnotations\ApiUserContext("agent")
     *
     * @ParamConverter("state", class="AppStoreBundle:Domain\ApplicationState", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppStateParamConverter")
     *
     * @param Entity\AppStore\AppState $state
     * @param string $scope
     * @return View\View
     */
    public function existsStateAction(Entity\AppStore\AppState $state = null, $scope)
    {
        $scopeObject = StateScope::parseString($scope);
        if (is_null($scopeObject) || !StateScope::isValid($scopeObject)) {
            throw new BadRequestHttpException('invalid scope');
        }

        if (is_null($state)) {
            return View\View::create([], Response::HTTP_NO_CONTENT);
        }

        return View\View::create([], Response::HTTP_OK);
    }

    /**
     * @Rest\Get("/{name}/{scope}")
     * @DeskproAnnotations\ApiUserContext("agent")
     *
     * @ParamConverter("state", class="AppStoreBundle:Domain\ApplicationState", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppStateParamConverter")
     * @ParamConverter("options", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppStateOptionsConverter")
     *
     * @param Entity\AppStore\AppState $state
     * @param string $scope
     * @param string|null $options
     * @return AppStateRepresentation|View\View
     */
    public function getStateAction(Entity\AppStore\AppState $state = null, $scope, $options = null)
    {

        $existingScope = StateScope::parseString($scope);
        if (is_null($existingScope) || !StateScope::isValid($existingScope)) {
            throw new BadRequestHttpException('invalid scope');
        }

        if (is_null($state) && $options === 'find') {
            return View\View::create([], Response::HTTP_NO_CONTENT);
        }

        if (is_null($state)) {
            throw new NotFoundHttpException('could not find state');
        }

        $actualScope = $state->getScope();
        if (!$actualScope->equals($existingScope)) {
            throw new NotFoundHttpException('could not find state');
        }

        if (
            $actualScope->getPermission() == AppStoreBundle\Domain\Constants::STATE_PERMISSION_PRIVATE
            && $state->getOwnerId() != $this->getUser()->getId()
        ) {
            throw new NotFoundHttpException('could not find state');
        }

        $representation = new AppStateRepresentation();
        $representation->mapFromState($state);

        return $representation;
    }

    /**
     * @Rest\Post("")
     * @DeskproAnnotations\ApiUserContext("agent")
     *
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\AppInstance", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppInstanceParamConverter")
     * @ParamConverter("representation", class="DeskPRO\Bundle\AppStoreBundle\API\AppStateRepresentation", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\SerializedParamConverter")
     * @param Entity\AppStore\AppInstance $application
     * @param AppStateRepresentation $representation
     * @return AppStateRepresentation
     */
    public function postStateAction(Entity\AppStore\AppInstance $application, AppStateRepresentation $representation)
    {
        $state = new Entity\AppStore\AppState();
        $representation->mapToAppStateEntity($state);
        $state->setAppInstance($application);

        if ($state->getScope()->getPermission() == AppStoreBundle\Domain\Constants::STATE_PERMISSION_PRIVATE) {
            $owner = $this->getUser();
            $state->setOwner($owner);
        }

        $em = $this->getManager();
        $em->persist($state);
        $em->flush();

        return $representation;
    }

    /**
     * @Rest\Put("/{name}")
     * @DeskproAnnotations\ApiUserContext("agent")
     *
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\AppInstance", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppInstanceParamConverter")
     * @ParamConverter("representation", class="DeskPRO\Bundle\AppStoreBundle\API\AppStateRepresentation", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\SerializedParamConverter")
     *
     * @param Entity\AppStore\AppInstance $application
     * @param AppStateRepresentation $representation
     * @return AppStateRepresentation
     */
    public function putStateAction(Entity\AppStore\AppInstance $application, AppStateRepresentation $representation = null)
    {
        if (is_null($representation)) {
            throw new BadRequestHttpException('invalid representation');
        }

        $state = new Entity\AppStore\AppState();
        $state->setAppInstance($application);
        $representation->mapToAppStateEntity($state);
        if ($state->getScope()->getPermission() == AppStoreBundle\Domain\Constants::STATE_PERMISSION_PRIVATE) {
            $owner = $this->getUser();
            $state->setOwner($owner);
        }

        $em = $this->getManager();
        $em->persist($state);
        $em->flush();

        return $representation;
    }

    /**
     * @Rest\Delete("/{name}/{scope}")
     * @DeskproAnnotations\ApiUserContext("agent")
     *
     * @ParamConverter("state", class="AppBundle:Entity\AppStore\AppState", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppStateParamConverter")
     * @param Entity\AppStore\AppState $state
     * @return AppStateRepresentation
     */
    public function deleteStateAction(Entity\AppStore\AppState $state)
    {
        $representation = new AppStateRepresentation();
        $representation->mapFromAppStateEntity($state);

        $em = $this->getManager();
        $em->remove($state);
        $em->flush();

        return $representation;
    }
}
