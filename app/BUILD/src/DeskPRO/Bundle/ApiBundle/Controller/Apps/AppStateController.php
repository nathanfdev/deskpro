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

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Entity;
use DeskPRO\Bundle\AppStoreBundle;
use DeskPRO\Bundle\AppStoreBundle\API\HttpExceptionConverter;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation as DeskproAnnotations;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AppsController
 *
 * @DeskproAnnotations\ApiModes("all")
 * @Rest\Route("/apps/{application}/state")
 */
class AppStateController extends BaseController
{
    /**
     * @Rest\Get("/{entityId}")
     * @DeskproAnnotations\ApiUserContext("agent")
     *
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\AppInstance", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppInstanceParamConverter")
     *
     * @param Entity\AppStore\AppInstance|null $application
     * @param $entityId
     * @return View\View
     */
    public function listStateAction(Entity\AppStore\AppInstance $application = null, $entityId)
    {
        $instanceId = null;
        if ($application instanceof Entity\AppStore\AppInstance) {
            $instanceId = $application->getId();
        }
        if (is_null($instanceId)) {
            throw new NotFoundHttpException('could not find state');
        }

        $entityIdentifier = AppStoreBundle\Domain\AppStorage\EntityId::parse($entityId);
        if (is_null($entityIdentifier)) {
            throw new BadRequestHttpException('invalid entity identifier');
        }

        $filter = new AppStoreBundle\Domain\AppStorageSearchFilter($instanceId, $entityId);

        $auth = $this->getUser();
        $accessRequest = AppStoreBundle\Infrastructure\AppStorage\ServiceAccessRequest::newAPIReadAccessRequest($auth);

        /** @var AppStoreBundle\Infrastructure\AppStorage\AccessService $stateAccessService */
        $stateAccessService = $this->container->get(AppStoreBundle\Infrastructure\AppStorage\AccessService::class);
        $list = $stateAccessService->readAllValues($filter, $accessRequest);

        $converter = function(AppStoreBundle\Domain\AppStorageItem $state) {
            return [
                "name" => $state->getName(),
                'app_id' => $state->getIdentifier()->getInstanceId(),
                "value" => json_decode($state->getValue(), $assoc = true)
            ];
        };
        $mappedValues = array_map($converter, $list);
        return View\View::create($mappedValues, Response::HTTP_OK);
    }

    /**
     * @Rest\Head("/{entityId}/{stateName}")
     * @DeskproAnnotations\ApiUserContext("agent")
     *
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\AppInstance", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppInstanceParamConverter")
     *
     * @param Entity\AppStore\AppInstance $application
     * @param $entityId
     * @param $stateName
     * @return View\View
     * @internal param Entity\AppStore\AppState $state
     */
    public function existsStateAction(Entity\AppStore\AppInstance $application = null, $entityId, $stateName)
    {
        $instanceId = null;
        $entityIdentifier = null;
        $stateIdentifer = null;

        if ($application instanceof Entity\AppStore\AppInstance) {
            $instanceId = $application->getId();
        }

        if (! is_null($instanceId)) {
            $entityIdentifier = AppStoreBundle\Domain\AppStorage\EntityId::parse($entityId);
        }

        if (!is_null($instanceId) && !is_null($entityIdentifier)) {
            $stateIdentifer = new AppStoreBundle\Domain\AppStorageItemIdentifier($instanceId, $stateName, $entityIdentifier);
        }

        if (is_null($stateIdentifer)) {
            return View\View::create([], Response::HTTP_NO_CONTENT);
        }

        $auth = $this->getUser();
        $accessRequest = AppStoreBundle\Infrastructure\AppStorage\PersonAccessRequest::newReadAccessRequest($auth);

        /** @var AppStoreBundle\Infrastructure\AppStorage\AccessService $stateAccessService */
        $stateAccessService = $this->container->get(AppStoreBundle\Infrastructure\AppStorage\AccessService::class);
        $isAvailable = $stateAccessService->allowsAccess($accessRequest, $stateIdentifer);

        if ($isAvailable) {
            return View\View::create([], Response::HTTP_OK);
        }
        return View\View::create([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Get("/{entityId}/{stateName}")
     * @DeskproAnnotations\ApiUserContext("agent")
     *
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\AppInstance", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppInstanceParamConverter")
     * @ParamConverter("options", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppStorageItemOptionsConverter")
     *
     * @param Entity\AppStore\AppInstance $application
     * @param $entityId
     * @param $stateName
     * @param array $options
     * @return View\View
     */
    public function getStateAction(Entity\AppStore\AppInstance $application = null, $entityId, $stateName, $options)
    {
        $instanceId = null;
        if ($application instanceof Entity\AppStore\AppInstance) {
            $instanceId = $application->getId();
        }
        if (is_null($instanceId)) {
            throw new NotFoundHttpException('could not find state');
        }

        $entityIdentifier = AppStoreBundle\Domain\AppStorage\EntityId::parse($entityId);
        if (is_null($entityIdentifier)) {
            throw new BadRequestHttpException('invalid entity identifier');
        }
        $stateIdentifer = new AppStoreBundle\Domain\AppStorageItemIdentifier($instanceId, $stateName, $entityIdentifier);

        $auth = $this->getUser();
        $accessRequest = AppStoreBundle\Infrastructure\AppStorage\ServiceAccessRequest::newAPIReadAccessRequest($auth);

        /** @var AppStoreBundle\Infrastructure\AppStorage\AccessService $stateAccessService */
        $stateAccessService = $this->container->get(AppStoreBundle\Infrastructure\AppStorage\AccessService::class);
        try {
            $value = $stateAccessService->readValue($stateIdentifer, $accessRequest);
            $responseBody = [ 'value' => json_decode($value, $assoc = true) ];

            return View\View::create($responseBody, Response::HTTP_OK);
        } catch (AppStoreBundle\Domain\AppStorage\Exception $e) {
            $stateNotFound = $e->getCode() === AppStoreBundle\Domain\AppStorage\Exception::CODE_STATE_NOT_FOUND;
            $returnHttpNoContent = array_key_exists('mode', $options) && $options['mode'] === 'find';

            if ($stateNotFound && $returnHttpNoContent) {
                return View\View::create([], Response::HTTP_NO_CONTENT);
            }
            throw HttpExceptionConverter::fromApplicationStateException($e);
        }
    }

    /**
     * @Rest\Put("/{entityId}/{stateName}")
     * @DeskproAnnotations\ApiUserContext("agent")
     *
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\AppInstance", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppInstanceParamConverter")
     *
     * @param Entity\AppStore\AppInstance $application
     * @param $entityId
     * @param $stateName
     * @param Request $request
     * @return View\View
     */
    public function putStateAction(Entity\AppStore\AppInstance $application = null, $entityId, $stateName, Request $request)
    {
        $instanceId = null;
        if ($application instanceof Entity\AppStore\AppInstance) {
            $instanceId = $application->getId();
        }
        if (is_null($instanceId)) {
            throw new NotFoundHttpException('could not find state');
        }

        $entityIdentifier = AppStoreBundle\Domain\AppStorage\EntityId::parse($entityId);
        if (is_null($entityIdentifier)) {
            throw new BadRequestHttpException('invalid entity identifier');
        }
        $stateIdentifer = new AppStoreBundle\Domain\AppStorageItemIdentifier($instanceId, $stateName, $entityIdentifier);

        // extract the state value from the request. somewhere before reaching this controller, JsonToFormDecoder
        // fails to handle a json encoded scalars, so we must wrap the state value in object... sad :(
        $stateValue = $request->getContent();
        $parsedStateValue = json_decode($stateValue, $associative = true);
        if (is_null($parsedStateValue) || !array_key_exists('value', $parsedStateValue)) {
            throw new BadRequestHttpException('could not decode value');
        }
        $value = json_encode($parsedStateValue['value']);

        $auth = $this->getUser();
        $accessRequest = AppStoreBundle\Infrastructure\AppStorage\ServiceAccessRequest::newAPIWriteAccessRequest($auth);

        /** @var AppStoreBundle\Infrastructure\AppStorage\AccessService $stateAccessService */
        $stateAccessService = $this->container->get(AppStoreBundle\Infrastructure\AppStorage\AccessService::class);
        try {
            $stateAccessService->writeValue($stateIdentifer, $accessRequest, $value);
            return View\View::create($parsedStateValue, Response::HTTP_OK);

        } catch (AppStoreBundle\Domain\AppStorage\Exception $e) {
            throw HttpExceptionConverter::fromApplicationStateException($e);
        }
    }

    /**
     * @Rest\Delete("/{entityId}/{stateName}")
     * @DeskproAnnotations\ApiUserContext("agent")
     *
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\AppInstance", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppInstanceParamConverter")
     *
     * @param Entity\AppStore\AppInstance $application
     * @param $entityId
     * @param $stateName
     * @return View\View
     */
    public function deleteStateAction(Entity\AppStore\AppInstance $application = null, $entityId, $stateName)
    {
        $instanceId = null;
        if ($application instanceof Entity\AppStore\AppInstance) {
            $instanceId = $application->getId();
        }
        if (is_null($instanceId)) {
            throw new NotFoundHttpException('could not find state');
        }

        $entityIdentifier = AppStoreBundle\Domain\AppStorage\EntityId::parse($entityId);
        if (is_null($entityIdentifier)) {
            throw new BadRequestHttpException('invalid entity identifier');
        }
        $stateIdentifer = new AppStoreBundle\Domain\AppStorageItemIdentifier($instanceId, $stateName, $entityIdentifier);

        $auth = $this->getUser();
        $accessRequest = AppStoreBundle\Infrastructure\AppStorage\ServiceAccessRequest::newAPIWriteAccessRequest($auth);

        /** @var AppStoreBundle\Infrastructure\AppStorage\AccessService $stateAccessService */
        $stateAccessService = $this->container->get(AppStoreBundle\Infrastructure\AppStorage\AccessService::class);
        try {
            $value = $stateAccessService->removeValue($stateIdentifer, $accessRequest);
            return View\View::create(json_decode($value, $assoc = true), Response::HTTP_OK);

        } catch (AppStoreBundle\Domain\AppStorage\Exception $e) {
            throw HttpExceptionConverter::fromApplicationStateException($e);
        }
    }
}
