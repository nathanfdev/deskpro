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
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AppsController
 *
 * @ApiModes("all")
 * @Rest\Route("/apps/{application}/state")
 */
class AppStateController extends BaseController
{
    /**
     * @Rest\GET("")
     *
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\AppInstance", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppInstanceParamConverter")
     * @ParamConverter("assetFilter", class="AppStoreBundle:Domain\AssetFilter", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\StateFilterParamConverter")
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

        return $list;
    }

    /**
     * @Rest\GET("/{name}/{scope}")
     *
     * @ParamConverter("stateId", class="AppStoreBundle:Domain\AssetFilter", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\StateFilterParamConverter")
     *
     * @param AppStoreBundle\Domain\ApplicationStateId $stateId
     * @param $scope
     */
    public function getStateAction(AppStoreBundle\Domain\ApplicationStateId $stateId, $scope)
    {

    }

    /**
     * @Rest\POST("")
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

        $em = $this->getManager();
        $em->persist($state);
        $em->flush();

        return $representation;
    }

    /**
     * @Rest\PUT("/{name}")
     *
     * @ParamConverter("state", class="AppBundle:Entity\AppStore\AppState", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppStateParamConverter")
     * @ParamConverter("representation", class="DeskPRO\Bundle\AppStoreBundle\API\AppStateRepresentation", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\SerializedParamConverter")
     * @param Entity\AppStore\AppState $state
     * @param AppStateRepresentation $representation
     * @return AppStateRepresentation
     */
    public function putStateAction(Entity\AppStore\AppState $state, AppStateRepresentation $representation)
    {
        $representation->mapToAppStateEntity($state);

        $em = $this->getManager();
        $em->persist($state);
        $em->flush();

        return $representation;
    }

    /**
     * @Rest\DELETE("/{name}")
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
