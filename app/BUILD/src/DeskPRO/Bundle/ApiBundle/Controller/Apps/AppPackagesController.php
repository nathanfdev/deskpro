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

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiUnstable;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\App;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\AppAssetBlob;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use DeskPRO\Bundle\AppStoreBundle\Domain\AppManifest;
use DeskPRO\Bundle\AppStoreBundle\Domain\AppManifestChanges\ChangeDetector;
use DeskPRO\Bundle\AppStoreBundle\Domain\AppManifestChanges\GenericChange;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\ApplicationManagerService;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppManifestReader;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

/**
 * Class AppPackagesController.
 *
 * @ApiModes("standard")
 * @Rest\Route("/apps/packages")
 * @ApiUnstable()
 * @ApiDoc(target="all", section="Apps", output="DeskPRO\Bundle\AppBundle\Entity\AppStore\App")
 */
class AppPackagesController extends CrudController
{
    public static $exposeOnly = ['get', 'list', 'count'];
    public static $entity     = App::class;


    /**
     * @ApiDoc(
     *      description="Get an App resource",
     *      tags={"CRUD"="#ffa500"},
     *      requirements={
     *          {
     *              "name"="application",
     *              "requirement"="^(?=.*[^\d].*)[^/]+$",
     *              "description"="The name of the application",
     *              "dataType"="string"
     *          }
     *      },
     *      statusCodes={
     *          200="We will return such status in case we found your entity",
     *          404="Not Found error will returned in case we can't find entity with specified name"
     *      },
     *     output="DeskPRO\Bundle\AppBundle\Entity\AppStore\App"
     * )

     * @Rest\Get("/{application}", requirements={"application"="^(?=.*[^\d].*)[^/]+$"})
     * @ParamConverter("app", class="AppBundle:Entity\AppStore\App", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppParamConverter", options={"attribute" = "application"})
     *
     * @param App $app
     * @param Request $request
     * @return View
     *
     */
    public function getByNameOrReferenceAction( App $app = null, Request $request)
    {
        if (empty($app)) {
            throw new NotFoundHttpException('could not find application');
        }

        $this->denyAccessUnlessGranted(PermissionGroupVoter::VIEW, $this->getPermissionGroupEntityContext($app->getId(), $request));
        return View::create($this->wrap($app), Response::HTTP_OK);
    }

    /**
     * @Rest\Put("/{application}/url", condition="request.headers.get('Content-Type') matches '#application/json#i'")
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\App", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppParamConverter")
     *
     * @param App $application
     */
    public function updateFromUrlAction(App $application = null)
    {
        if (empty($application)) {
            throw new NotFoundHttpException('could not find application');
        }

        throw new ServiceUnavailableHttpException('endpoint not available');
    }

    /**
     * @Rest\Get("/{application}/manifest")
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\App", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppParamConverter")
     *
     * @param App $application
     *
     * @return AppManifest
     */
    public function getManifestAction(App $application = null)
    {
        if (empty($application)) {
            throw new NotFoundHttpException('could not find application');
        }

        return $application->getManifest();
    }

    /**
     * @Rest\Get("/{application}/manifest-changes")
     *
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\App", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppParamConverter")
     *
     * @param App $application
     *
     * @return array|object[]
     */
    public function getManifestChangesAction(App $application = null)
    {
        if (empty($application)) {
            throw new NotFoundHttpException('could not find application');
        }

        $manifest = $application->getManifest();
        /** @var AppAssetBlob $previousManifestAsset */
        $previousManifestAsset = $application->getAssets()->filter((function (AppAssetBlob $asset) {
            return $asset->getPath() === '.deskpro/versions/manifest.json.prev';
        }))->first();

        if (!$previousManifestAsset) {
            $previousManifest = new AppManifest();
        } else {
            /** @var ApplicationManagerService $instanceManager */
            $instanceManager = $this->container->get('apps2.application_manager');
            $previousManifest = $instanceManager->readManifestFromAssetBlob($previousManifestAsset);
        }

        $changeDetector = new ChangeDetector();
        $changes = [];
        $changes = array_merge($changes, $changeDetector->customFieldChanges($manifest, $previousManifest));
        $changes = array_merge($changes, $changeDetector->settingsChanges($manifest, $previousManifest));

        return $changes;
    }

}
