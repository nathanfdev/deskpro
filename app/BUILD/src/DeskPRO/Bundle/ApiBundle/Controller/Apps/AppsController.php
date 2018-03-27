<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Apps;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiUnstable;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\AppStoreBundle;
use DeskPRO\Bundle\AppStoreBundle\Domain\AppManifest;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\ApplicationManagerService;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\ApplicationRemovalService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class AppsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/apps")
 * @ApiUnstable()
 * @ApiDoc(
 *     target="updateAppAction",
 *     input={
 *      "class"="DeskPRO\Bundle\ApiBundle\Controller\Apps\ApplicationStatus",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\CustomDefTicket"
 *      }
 *     },
 *     output="DeskPRO\Bundle\AppBundle\Entity\AppStore\App",
 *     statusCodes={
 *         200="Application updated",
 *         400="Invalid request"
 *     }
 *  )
 */
class AppsController extends BaseController
{
    /**
     * @ApiDoc(
     *   section="Apps",
     *   description="Returns a list of application instances",
     *   output="array<DeskPRO\Bundle\AppStoreBundle\Domain\ApplicationInstance>"
     * )
     *
     * @Rest\Get("")
     *
     * @param HttpFoundation\Request $request
     *
     * @return string
     */
    public function listApplicationAction(HttpFoundation\Request $request)
    {
        //convert query params into a filter
        $searchFilter = new AppStoreBundle\Domain\SearchApplicationInstanceFilter();
        $parameterBag = $request->attributes;
        if ($parameterBag->has('scope')) {
            $searchFilter->setScope($parameterBag->get('scope'));
        }

        $queryParams = $request->query;
        if ($queryParams->has('isInstalled')) {
            $searchFilter->setIsInstalled(filter_var($queryParams->get('isInstalled'), FILTER_VALIDATE_BOOLEAN));
        }
        if ($queryParams->has('isDev')) {
            $searchFilter->setIsDev(filter_var($queryParams->get('isDev'), FILTER_VALIDATE_BOOLEAN));
        }

        /** @var AppStoreBundle\Domain\ApplicationInstanceFinder $instanceFinder */
        $instanceFinder = $this->container->get(AppStoreBundle\Domain\ApplicationInstanceFinder::class);
        if ($searchFilter->isEmpty()) {
            $instances = $instanceFinder->findAll();
        } else {
            $instances = $instanceFinder->findByFilter($searchFilter);
        }

        return $this->wrap($instances);
    }

    /**
     * @Rest\Get("/{application}", name="api_get_app_instance")
     *
     * @param Entity\AppStore\AppInstance|null $application
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\AppInstance", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppInstanceParamConverter")
     *
     * @return string
     */
    public function getApplicationAction(Entity\AppStore\AppInstance $application = null)
    {
        if (empty($application)) {
            throw new NotFoundHttpException('could not find application');
        }

        return $this->wrap($application);
    }

    /**
     * @Rest\Post("", condition="request.headers.get('Content-Type') matches '#application/zip#i'")
     * @ParamConverter("bundle", class="AppStoreBundle:Infrastructure\AppZipArchiveBundle", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppZipArchiveBundleParamConverter")
     *
     * @param AppStoreBundle\Infrastructure\AppZipArchiveBundle $bundle
     *
     * @return string
     */
    public function createFromZipFileAction(AppStoreBundle\Infrastructure\AppZipArchiveBundle $bundle)
    {
        /** @var AppStoreBundle\Domain\AppBundleValidator $bundleValidator */
        $bundleValidator = $this->container->get(AppStoreBundle\Domain\AppBundleValidator::class);
        $validBundle     = $bundleValidator->validateBundle($bundle);

        if (!$validBundle) { //TODO provide a more elaborate exception body
            throw new UnprocessableEntityHttpException('invalid bundle');
        }

        $instanceCreator = $this->container->get('apps2.application_manager');
        $instance        = $instanceCreator->createFirstInstance($bundle);

        return $instance;
    }

     /**
      * @Rest\Post("/{application}")
      * @ParamConverter("application", class="AppBundle:Entity\AppStore\App", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppParamConverter", options={"numericId" = "instanceId"})
      */
     public function createAction(Entity\AppStore\App $application = null)
     {
         if (empty($application)) {
             throw new NotFoundHttpException('could not find application');
         }

         /** @var ApplicationManagerService $instanceCreator */
         $instanceCreator = $this->container->get('apps2.application_manager');

         $isSingle = $application->getManifest()->isSingle();
         if ($isSingle) {
             /** @var AppStoreBundle\Domain\ApplicationInstanceFinder $instanceFinder */
             $instanceFinder = $this->container->get(AppStoreBundle\Domain\ApplicationInstanceFinder::class);
             $instance       = $instanceFinder->findSoleApplicationInstance($application->getName());

             if (!is_null($instance)) {
                 throw new ConflictHttpException('application can only have one instance');
             }
         }

         $instance = $instanceCreator->createInstance($application);

         return $this->wrap($instance);
     }

    /**
     * @Rest\Put("/{application}", condition="request.headers.get('Content-Type') matches '#application/json#i'")
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\App", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppInstanceParamConverter")
     *
     * @param Entity\AppStore\AppInstance $application
     *
     * @return \DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper
     */
    public function updateAppAction(Entity\AppStore\AppInstance $application = null, Request $request)
    {
        if (empty($application)) {
            throw new NotFoundHttpException('could not find application');
        }

        //parse response body
        $body           = $request->getContent();
        $representation = json_decode($body, $associative = true);

        $properties = [
            'is_installed' => function (Entity\AppStore\AppInstance $app, $value) {
                if (is_bool($value)) {
                    $app->setIsInstalled($value);

                    return true;
                }

                return false;
            },
        ];

        if (!is_array($representation)) {
            throw new BadRequestHttpException('could not decode value');
        }

        $validRequest = false;
        foreach ($properties as $name => $mapper) {
            if (array_key_exists($name, $representation)) {
                $validRequest = $validRequest | $mapper($application, $representation[$name]);
            }
        }

        if (!$validRequest) {
            throw new BadRequestHttpException('invalid representation');
        }

        $em = $this->getManager();
        $em->persist($application);
        $em->flush();

        $this->container
            ->get('event_dispatcher')
            ->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent('agent.ui.reload', [
                'type'        => 'admin',
                'person_id'   => 0,
                'person_name' => 'System',
            ]));

        return $this->wrap($application);
    }

    /**
     * @Rest\Delete("/{application}")
     *
     * @param Entity\AppStore\AppInstance $application
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\AppInstance", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppInstanceParamConverter")
     *
     * @return View
     */
    public function deleteApplicationAction(Entity\AppStore\AppInstance $application = null)
    {
        if (empty($application)) {
            throw new NotFoundHttpException('could not find application');
        }

        /** @var AppStoreBundle\Infrastructure\ApplicationManagerService $appManager */
        $appManager = $this->container->get('apps2.application_manager');
        $strategy   = $appManager->getRemoveStrategy($application);

        /** @var ApplicationRemovalService $removalManager */
        $removalManager = $this->container->get(ApplicationRemovalService::class);
        $removalManager->remove($application, $strategy);

        $this->container
            ->get('event_dispatcher')
            ->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent('agent.ui.reload', [
                'type'        => 'admin',
                'person_id'   => 0,
                'person_name' => 'System',
            ]));

        return new View(null, HttpFoundation\Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Get("/{application}/manifest")
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\App", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppInstanceParamConverter")
     *
     * @param Entity\AppStore\AppInstance $application
     *
     * @return AppManifest
     */
    public function getManifestAction(Entity\AppStore\AppInstance $application = null)
    {
        if (empty($application)) {
            throw new NotFoundHttpException('could not find application');
        }

        return $application->getApp()->getManifest();
    }

    /**
     * @Rest\Get("/{application}/settings")
     *
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\AppInstance", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppInstanceParamConverter")
     *
     * @param Entity\AppStore\AppInstance $application
     *
     * @return array
     */
    public function getSettingsAction(Entity\AppStore\AppInstance $application = null)
    {
        if (empty($application)) {
            throw new NotFoundHttpException('could not find application');
        }

        return $application->getSettings();
    }

    /**
     * @Rest\Get("/{application}/assets")
     *
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\App", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppInstanceParamConverter")
     * @ParamConverter("searchFilter", class="AppStoreBundle:Domain\AssetFilter", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AssetFilterParamConverter")
     *
     * @param Entity\AppStore\AppInstance             $application
     * @param AppStoreBundle\Domain\SearchAssetFilter $searchFilter
     */
    public function listAssetsAction(Entity\AppStore\AppInstance $application = null, AppStoreBundle\Domain\SearchAssetFilter $searchFilter)
    {
        if (empty($application)) {
            throw new NotFoundHttpException('could not find application');
        }

        $app = $application->getApp();
        /** @var AppStoreBundle\Domain\AssetFinder $assetFinder */
        $assetFinder = $this->container->get(AppStoreBundle\Domain\AssetFinder::class);
        $assets      = $assetFinder->findApplicationAssets($app, $searchFilter);

        return $assets;
    }

    /**
     * @Rest\Get("/{application}/status")
     *
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\App", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppInstanceParamConverter")
     *
     * @param Entity\AppStore\AppInstance $application
     *
     * @return ApplicationStatus
     */
    public function getStatusAction(Entity\AppStore\AppInstance $application = null)
    {
        if (empty($application)) {
            throw new NotFoundHttpException('could not find application');
        }

        $status = new ApplicationStatus();
        $status->setIsDev($application->getApp()->getIsDev());
        $status->setIsInstalled($application->getIsInstalled());

        return $status;
    }
}
