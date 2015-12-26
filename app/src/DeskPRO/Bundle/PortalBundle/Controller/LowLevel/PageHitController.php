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

namespace DeskPRO\Bundle\PortalBundle\Controller\LowLevel;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PageHitController extends BaseController
{
    /**
     * @Route(
     *     "/dp/hit/{page_type}/{page_id}.{_format}",
     *     requirements={
     *         "page_type"="[a-zA-Z_\-0-9\.]+",
     *         "page_id"="[a-zA-Z_\-0-9\.]+",
     *         "_format"="txt|html|json|png|gif"
     *     },
     *     defaults={"page_id"="0"},
     *     name="dp_pagehit"
     * )
     * @Method({"GET", "POST", "OPTIONS"})
     *
     * @param string  $page_type
     * @param string  $page_id
     * @param Request $request
     *
     * @return Response
     */
    public function hitAction($page_type, $page_id, Request $request)
    {
        if ($request->getMethod() === 'OPTIONS') {
            return new Response('', 200, [
                'Content-Type'                 => 'text/plain',
                'Access-Control-Allow-Origin'  => '*',
                'Access-Control-Allow-Methods' => 'GET,POST',
            ]);
        }

        try {
            $hit = $this->get('hitrecord.record_factory')->fromRequest(
                $page_type,
                $page_id,
                $request,
                $this->get('visitor_identification_provider')->getVisitorIdentifier()
            );
        } catch (\Exception $e) {
            throw $this->createNotFoundException($e->getMessage());
        }

        $id = $this->get('hitrecord.record_storage')->record($hit);

        switch ($request->getRequestFormat('json')) {
            case 'text':
            case 'txt':
                $resData = 'hit_id='.$id;
                $resType = 'text/plain';
                break;
            case 'html':
                $resData = 'hit_id='.$id;
                $resType = 'text/html';
                break;
            case 'json':
                $resData = json_encode(['hit_id' => $id]);
                $resType = 'application/json';
                break;
            case 'png':
                $resData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQYV2NgYAAAAAMAAWgmWQ0AAAAASUVORK5CYII=');
                $resType = 'image/png';
                break;
            case 'gif':
                $resData = base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');
                $resType = 'image/gif';
                break;
            default:
                throw $this->createNotFoundException();
        }

        $res = new Response($resData, 200, ['Content-Type' => $resType]);
        $res->setPrivate();
        $res->setMaxAge(0);
        $res->setLastModified(new \DateTime());

        return $res;
    }
}
