<?php

namespace DeskPRO\Bundle\PortalBundle\Controller\Api;

use Application\DeskPRO\Attachments\AcceptAttachment;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BlobsController.
 */
class BlobsController extends AbstractApiController
{
    /**
     * @Route("/portal/api/blobs/temp", name="portal_api_blobs_temp")
     * @Method({"POST"})
     *
     * @param Request $request
     *
     * @return View
     */
    public function tempAction(Request $request)
    {
        /** @var AcceptAttachment $accept */
        $accept = $this->getContainer()->getAttachmentAccepter();
        $blobs  = [];

        foreach ($request->files->get('files') as $file) {
            $blobs[] = $accept->accept($file);
        }

        return new View($this->wrap($blobs));
    }

    /**
     * @Route("/portal/api/dpblob", name="portal_api_blob_upload")
     * @Method("POST")
     *
     * @return JsonResponse
     */
    public function uploadBlobAction()
    {
        return $this->forward('PortalBundle:Portal:uploadBlob');
    }
}
