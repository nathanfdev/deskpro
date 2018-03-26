<?php

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
