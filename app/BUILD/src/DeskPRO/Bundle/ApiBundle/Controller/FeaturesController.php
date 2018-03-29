<?php

namespace DeskPRO\Bundle\ApiBundle\Controller;

use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\JobQueue\Processor\FeatureProcessor;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Model\Feature;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Features\BetaFeatureInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class FeaturesController.
 *
 * @ApiModes({"session", "key"})
 * @Rest\Route("/features")
 * @ApiUserContext("admin")
 */
class FeaturesController extends BaseController
{
    /**
     * Fetch available features list.
     *
     * @ApiDoc(
     *     section="Features",
     *     resourceDescription="Operations about features",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="array<DeskPRO\Bundle\ApiBundle\Model\Feature>"
     * )
     *
     * @return View
     * @Rest\Get("")
     */
    public function getFeaturesAction()
    {
        $collection = $this->get('deskpro.features_collection');
        $features   = [];

        foreach ($collection->getAvailableFeatures() as $feature) {
            $features[] = $this->getFeatureModel($feature);
        }

        return View::create($this->wrap($features));
    }

    /**
     * Get specific feature.
     *
     * @ApiDoc(
     *     section="Features",
     *     resourceDescription="Operations about features",
     *     requirements={
     *          {
     *              "name"="id",
     *              "requirement"="[a-zA-Z0-9\\.\\-_]+",
     *              "description"="id of the feature",
     *              "dataType"="string"
     *          }
     *      },
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="DeskPRO\Bundle\ApiBundle\Model\Feature"
     * )
     *
     * @Rest\Get("/{feature}")
     * @ParamConverter(name="feature", converter="feature")
     *
     * @param BetaFeatureInterface $feature
     *
     * @return View
     */
    public function getFeatureAction(BetaFeatureInterface $feature)
    {
        return View::create($this->wrap($this->getFeatureModel($feature)));
    }

    /**
     * Queue feature enabling.
     *
     * @ApiDoc(
     *     section="Features",
     *     resourceDescription="Operations about features",
     *     requirements={
     *          {
     *              "name"="id",
     *              "requirement"="[a-zA-Z0-9\\.\\-_]+",
     *              "description"="id of the feature",
     *              "dataType"="string"
     *          }
     *      },
     *     statusCodes={
     *         204="Returned if everything is ok",
     *         400="Returned if feature already enabled"
     *     },
     *     noInput=true
     * )
     *
     * @Rest\Put("/{feature}/enable")
     * @ParamConverter(name="feature", converter="feature")
     *
     * @param BetaFeatureInterface $feature
     *
     * @return View
     */
    public function enableFeatureAction(BetaFeatureInterface $feature)
    {
        if ($feature->isEnabled()) {
            throw new BadRequestHttpException(sprintf('Feature %s already enabled!', $feature->getTitle()));
        }

        $this->createProcessingFlag($feature, 'feature_enable');
        $this->get('job.queue')->add(FeatureProcessor::JOB_TYPE, [
            'feature_id' => $feature->getId(),
            'action'     => 'enable',
        ]);

        return View::create(null, Response::HTTP_CREATED);
    }

    /**
     * Queue feature disabling.
     *
     * @ApiDoc(
     *     section="Features",
     *     resourceDescription="Operations about features",
     *     requirements={
     *          {
     *              "name"="id",
     *              "requirement"="[a-zA-Z0-9\\.\\-_]+",
     *              "description"="id of the feature",
     *              "dataType"="string"
     *          }
     *      },
     *     statusCodes={
     *         200="Returned if everything is ok",
     *         400="Returned if feature already disabled"
     *     },
     *     noInput=true
     * )
     *
     * @Rest\Put("/{feature}/disable")
     * @ParamConverter(name="feature", converter="feature")
     *
     * @param BetaFeatureInterface $feature
     *
     * @return View
     */
    public function disableFeatureAction(BetaFeatureInterface $feature)
    {
        if (!$feature->isEnabled()) {
            throw new BadRequestHttpException(sprintf('Feature %s already disabled!', $feature->getTitle()));
        }

        $this->createProcessingFlag($feature, 'feature_disable');
        $this->get('job.queue')->add(FeatureProcessor::JOB_TYPE, [
            'feature_id' => $feature->getId(),
            'action'     => 'disable',
        ]);

        return View::create(null, Response::HTTP_CREATED);
    }

    /**
     * @param BetaFeatureInterface $feature
     *
     * @return Feature
     */
    private function getFeatureModel(BetaFeatureInterface $feature)
    {
        $tmpData = $this->getManager()->getRepository(TmpData::class)->findOneBy(
            ['name' => sprintf('%s.%s', BetaFeatureInterface::BETA_FEATURES_KEY, $feature->getId())],
            ['date_expire' => 'DESC']
        );

        $processing = false;
        if ($tmpData) {
            $processing = $tmpData->getDateExpire()->getTimestamp() > time();
        }

        return new Feature($feature, $processing);
    }

    /**
     * @param BetaFeatureInterface $feature
     * @param string               $type
     */
    private function createProcessingFlag(BetaFeatureInterface $feature, $type)
    {
        $key     = sprintf('%s.%s', BetaFeatureInterface::BETA_FEATURES_KEY, $feature->getId());
        $tmpData = TmpData::create($type, ['feature_id' => $feature->getId()], '+20min', $key);

        $em = $this->getManager();
        $em->persist($tmpData);
        $em->flush();
    }
}
