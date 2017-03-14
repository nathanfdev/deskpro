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

namespace DeskPRO\Bundle\PortalBundle\Controller\Api;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class GuidesController.
 *
 * @Feature("guides")
 */
class GuidesController extends AbstractApiController
{
    /**
     * @Route("/portal/api/guides/topic/{slug}", name="portal_api_guides_topic")
     * @Method({"GET"})
     *
     * @param $slug
     *
     * @return View|NotFoundHttpException
     */
    public function getTopicAction($slug)
    {
        $topic = $this->get('data.guides')->getTopicBySlug($slug);
        if (!$topic) {
            return $this->createNotFoundException();
        }

        return new View($this->wrap($topic), Response::HTTP_OK);
    }

    /**
     * @Route("/portal/api/guides/guide/{slug}", name="portal_api_guides_guide")
     * @Method({"GET"})
     *
     * @param $slug
     *
     * @return View|NotFoundHttpException
     */
    public function getGuideAction($slug)
    {
        $guide = $this->get('data.guides')->getGuideBySlug($slug);
        if (!$guide) {
            return $this->createNotFoundException();
        }

        return new View($this->wrap($guide), Response::HTTP_OK);
    }

    /**
     * @Route("/portal/api/guides/topics/{slug}", name="portal_api_guides_topics")
     * @Method({"GET"})
     *
     * @param $slug
     *
     * @return View|NotFoundHttpException
     */
    public function getGuideTopicsAction($slug)
    {
        $guideDataService = $this->get('data.guides');
        $guide            = $guideDataService->getGuideBySlug($slug);
        if (!$guide) {
            return $this->createNotFoundException();
        }
        $person = $this->getUser();

        $topics = $guideDataService->getGuideChildren(
            $guide,
            $person
        );

        return new View($this->wrap($topics), Response::HTTP_OK);
    }
}
