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

/**
 * DeskPRO.
 */
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
        };

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
