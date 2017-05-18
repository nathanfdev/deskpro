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

        $em   = $this->get('doctrine.orm.default_entity_manager');
        $repo = $em->getRepository(TmpData::class);

        foreach ($collection->getAvailableFeatures() as $feature) {
            /** @var BetaFeatureInterface $feature */
            $tmpData = $repo
                ->findOneBy(
                    ['name' => sprintf('%s.%s', BetaFeatureInterface::BETA_FEATURES_KEY, $feature->getId())],
                    ['date_expire' => 'DESC']
                );
            $processing = $tmpData && $tmpData->date_expire->getTimestamp() > time();
            $features[] = new Feature($feature, $processing);
        }

        return View::create(
            $this->wrap($features),
            Response::HTTP_OK
        );
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
     * @param string $id
     *
     * @return View
     * @Rest\Get("/{id}")
     */
    public function getFeatureAction($id)
    {
        $collection = $this->get('deskpro.features_collection');
        $feature    = $collection->getFeature($id);

        $em      = $this->get('doctrine.orm.default_entity_manager');
        $repo    = $em->getRepository(TmpData::class);
        $tmpData = $repo
            ->findOneBy(
                ['name' => sprintf('%s.%s', BetaFeatureInterface::BETA_FEATURES_KEY, $id)],
                ['date_expire' => 'DESC']
            );
        $processing = $tmpData && $tmpData->date_expire->getTimestamp() > time();

        return View::create(
            $this->wrap($feature ? new Feature($feature, $processing) : []),
            Response::HTTP_OK
        );
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
     * @param string $id
     *
     * @return View
     * @Rest\Put("/{id}/enable")
     */
    public function enableFeatureAction($id)
    {
        $collection = $this->get('deskpro.features_collection');
        $feature    = $collection->getFeature($id);

        if ($feature->isEnabled()) {
            throw new BadRequestHttpException(sprintf('Feature %s already enabled!', $feature->getTitle()));
        }

        $this->get('job.queue')->add(FeatureProcessor::JOB_TYPE, ['feature_id' => $id, 'action' => 'enable']);

        $em      = $this->get('doctrine.orm.default_entity_manager');
        $key     = sprintf('%s.%s', BetaFeatureInterface::BETA_FEATURES_KEY, $id);
        $tmpData = TmpData::create('feature_enable', ['feature_id' => $id], '+20min', $key);
        $em->persist($tmpData);
        $em->flush();

        return View::create(
            null,
            Response::HTTP_CREATED
        );
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
     * @param string $id
     *
     * @return View
     * @Rest\Put("/{id}/disable")
     */
    public function disableFeatureAction($id)
    {
        $collection = $this->get('deskpro.features_collection');
        $feature    = $collection->getFeature($id);

        if (!$feature->isEnabled()) {
            throw new BadRequestHttpException(sprintf('Feature %s already disabled!', $feature->getTitle()));
        }

        $this->get('job.queue')->add(FeatureProcessor::JOB_TYPE, ['feature_id' => $id, 'action' => 'disable']);

        $em      = $this->get('doctrine.orm.default_entity_manager');
        $key     = sprintf('%s.%s', BetaFeatureInterface::BETA_FEATURES_KEY, $id);
        $tmpData = TmpData::create('feature_disable', ['feature_id' => $id], '+20min', $key);
        $em->persist($tmpData);
        $em->flush();

        return View::create(
            null,
            Response::HTTP_CREATED
        );
    }
}
