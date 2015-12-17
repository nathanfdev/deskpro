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
namespace DeskPRO\Bundle\PortalBundle\Controller\Api;

use DeskPRO\Component\Util\MapUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
    public function createNewChatAction(Request $request)
    {
        static $phrases = [
            'portal.tickets.related_articles_title',
            'portal.tickets.related_articles_desc',
            'portal.general.prop_date',
            'portal.general.prop_views',
            'portal.general.prop_rating',
            'portal.general.prop_popularity',
            'portal.general.prop_comments',
            'portal.general.select_placeholder',
            'portal.account.login-invalid',
            'portal.account.login-password',
            'portal.account.login-password-reminder',
            'portal.general.show_x_more',
        ];

        $tr = $this->container->get('deskpro.core.translate');

        $output = MapUtils::map($phrases, function ($idx, $id) use ($tr) {
            return [$id, $tr->getPhraseText($id)];
        });

        $res = new JsonResponse($output);

        $format = $request->getRequestFormat('json');
        if ($format === 'js') {
            $cb = $request->request->get('callback', 'DP_SET_PHRASES');
            $res->setCallback($cb);
        }

        // TODO proper caching headers here, phrases should reload:
        // - new version
        // - when langs are updated (need some global uid that changes when admin edits phrase)

        return $res;
    }
}
