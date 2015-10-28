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

namespace DeskPRO\Bundle\ApiBundle\Controller\Blobs;

use Application\DeskPRO\Entity\Blob;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TempController.
 */
class TempController extends BaseController
{
    /**
     * @Post("/blobs/temp", name="api_post_blobs_temp")
     *
     * @param Request $request
     *
     * @return View
     */
    public function postAction(Request $request)
    {
        $blob = new Blob();

        return View::create(
            [
                'blob_id'           => $blob['id'],
                'blob_auth'         => $blob->authcode,
                'blob_auth_id'      => $blob->id.'-'.$blob->authcode,
                'download_url'      => $blob->getDownloadUrl(true, false),
                'filename'          => $blob['filename'],
                'filesize_readable' => $blob->getReadableFilesize(),
                'is_image'          => $blob->isImage(),
            ],
            Response::HTTP_CREATED
        );
    }
}
