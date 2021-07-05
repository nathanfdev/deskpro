<?php

namespace DeskPRO\Bundle\PortalBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LanguageController.
 */
class LanguageController extends AbstractApiController
{
    /**
     * @Route("/portal/api/lang/widget-phrases.{_format}", name="portal_api_lang_widget_phrases", requirements={"_format":"json|js"})
     * @Method({"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function widgetPhrasesAction(Request $request)
    {
        return $this->getResponse($request);
    }

    /**
     * @param Request $request
     * @param array   $phrases
     *
     * @return JsonResponse
     */
    protected function getResponse(Request $request)
    {
        $languageId = $request->query->getInt('language');
        $serialized = $this->get('dp.portal.languages.widget_phrase_translator')->translatePhrases($languageId);
        $response   = new JsonResponse($serialized);

        if ($request->getRequestFormat('json') === 'js') {
            $response->headers->set('Content-Type', 'application/javascript');
            $cb = $request->request->get('callback', 'DP_SET_PHRASES');
            $response->setCallback($cb);
        }

        // TODO proper caching headers here, phrases should reload:
        // - new version
        // - when langs are updated (need some global uid that changes when admin edits phrase)

        return $response;
    }
}
