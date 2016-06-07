<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\PortalBundle\Controller\Api\PortalDesigner;

use DeskPRO\Bundle\PortalBundle\Controller\Api\AbstractApiController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AdvancedEditsController.
 *
 * @Rest\Route("/portal/api/style/edit-theme-set/advanced-edits")
 */
class AdvancedEditsController extends AbstractApiController
{
    use HelperMethods;

    /**
     * @Rest\Get("")
     *
     * @return View
     */
    public function getAdvancedEditsAction()
    {
        return new View($this->getAdvancedEditsManager()->get());
    }

    /**
     * @Rest\Put("")
     *
     * @param Request $request
     *
     * @return View
     */
    public function saveAdvancedEditsAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $this->getAdvancedEditsManager()->save($data);

        return new View(null, Response::HTTP_NO_CONTENT);
    }
}
