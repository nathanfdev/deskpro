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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\PortalBundle\Controller\Api\PortalDesigner;

use Application\DeskPRO\Entity\PersonEmail;
use DeskPRO\Bundle\PortalBundle\Controller\Api\AbstractApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class EmailsController.
 */
class EmailsController extends AbstractApiController
{
    use HelperMethods;

    /**
     * @Route("/portal/api/emails")
     * @Method({"GET"})
     */
    public function searchEmailsAction(Request $request)
    {
        $term   = $request->get('term');
        $target = $request->get('target');

        $repository = $this->getManager()->getRepository(PersonEmail::class);
        switch ($target) {
            case 'user':
                $emails = $repository->searchUserEmails($term);
                break;
            case 'agent':
                $emails = $repository->searchAgentEmails($term);
                break;
            default:
                throw new BadRequestHttpException("Unknown email target $target");
        }

        return new JsonResponse($emails);
    }
}
