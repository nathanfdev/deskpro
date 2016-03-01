<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiTags;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TagsController.
 *
 * @ApiModes("all")
 */
class TagsController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="get api logs collection",
     *      statusCodes={
     *          200="Success",
     *      }
     * )
     * @Annotations\Get("/api_tags/{id}", name="api_tags_list_for_key")
     *
     * @param int $id
     *
     * @return Response
     */
    public function cgetAction($id)
    {
        $tags_collector = $this->get('api_authorization.tags_collector');
        $tags_collector->collectTags(true);

        return View::create(
            $this->dataSerialize($tags_collector->getTagsHierarchyForApi($id)),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="get api logs collection",
     *      statusCodes={
     *          200="Success",
     *      }
     * )
     * @Annotations\Put("/api_tags/{id}", name="api_tags_put", requirements={"id": "\d+"})
     *
     * @param Request $request
     * @param int     $id
     * @ ApiTags("superuser")
     */
    public function putAction(Request $request, $id)
    {
        $value     = $request->request->getInt('value');
        $action    = $request->request->get('action');
        $collector = $this->get('api_authorization.tags_collector');
        $collector->updateTags($id, $action, $value);
    }
}
