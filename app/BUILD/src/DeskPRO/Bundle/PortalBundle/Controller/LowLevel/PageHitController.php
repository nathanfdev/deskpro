<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Controller\LowLevel;

use DeskPRO\Bundle\PortalBundle\Visitor\VisitorIdentificationProvider;
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
            $visitorId = $this->get('visitor_identification_provider')->getVisitorIdentifier(true);
            $request->attributes->set(VisitorIdentificationProvider::ATTRIBUTE_NAME, $visitorId);

            $hit = $this->get('hitrecord.record_factory')->fromRequest(
                $page_type,
                $page_id,
                $request,
                $visitorId
            );
        } catch (\Exception $e) {
            throw $this->createNotFoundException($e->getMessage());
        }

        $this->get('hitrecord.record_storage')->record($hit);

        switch ($request->getRequestFormat('json')) {
            case 'text':
            case 'txt':
                $resData = 'ok';
                $resType = 'text/plain';
                break;
            case 'html':
                $resData = 'ok';
                $resType = 'text/html';
                break;
            case 'json':
                $resData = json_encode(['ok' => 'ok']);
                $resType = 'application/json';
                break;
            case 'js':
                $resData = '// ok';
                $resType = 'text/javascript';
                break;
            case 'css':
                $resData = '/* ok */';
                $resType = 'text/css';
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
