<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Controller;

use Application\DeskPRO\Entity\Language;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Component\Util\MapUtils;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * API access to languages.
 *
 * @ApiModes("all")
 * @Rest\Route("/languages")
 * @ApiDoc(target="all", section="Languages", output="Application\DeskPRO\Entity\Language")
 */
class LanguagesController extends CrudController
{
    public static $entity     = Language::class;
    public static $listOrder  = 'asc';
    public static $exposeOnly = ['list', 'get'];

    /**
     * @ApiDoc(
     *      section="Languages",
     *      description="provide agent phrases for frontend",
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      }
     * )
     * @Rest\Get("/agent_phrases")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function agentPhrasesAction(Request $request)
    {
        $phrases = [
            'agent.chat.online_for_chat',
            'agent.chat.notification_volume',
            'agent.chat.by_department',
            'agent.chrome.link_help',
            'agent.chrome.link_logout',
            'agent.chrome.link_preferences',
            'agent.chrome.nav_search',
            'agent.chrome.notification_tooltip',
            'agent.chrome.recent_tooltip',
            'agent.chrome.user_tooltip',
            'agent.chrome.view_tooltip',
            'agent.general.article',
            'agent.general.chat',
            'agent.general.download',
            'agent.general.feedback',
            'agent.general.news_post',
            'agent.general.off',
            'agent.general.on',
            'agent.general.organization',
            'agent.general.person',
            'agent.general.task',
            'agent.general.ticket',
            'agent.general.topic',
            'agent.general.your_profile',
            'agent.onboarding.topbar_search_title',
            'agent.onboarding.topbar_search_text',
            'agent.onboarding.topbar_history_title',
            'agent.onboarding.topbar_history_text',
            'agent.onboarding.topbar_create_title',
            'agent.onboarding.topbar_create_text',
            'agent.onboarding.topbar_views_title',
            'agent.onboarding.topbar_views_text',
            'agent.onboarding.topbar_notifications_title',
            'agent.onboarding.topbar_notifications_text',
            'agent.onboarding.topbar_chat_title',
            'agent.onboarding.topbar_chat_text',
            'agent.onboarding.topbar_profile_title',
            'agent.onboarding.topbar_profile_text',
            'agent.onboarding.topbar_intro_title',
            'agent.onboarding.topbar_intro_text',
            'agent.onboarding.topbar_intro_button',
            'agent.onboarding.topbar_changes_intro_title',
            'agent.onboarding.topbar_changes_intro_text',
            'agent.tickets.count_agents',
            'agent.voice.incoming_call_title',
            'agent.voice.call_new_incoming',
            'agent.voice.call_new_outgoing',
            'agent.voice.call_answered',
            'agent.voice.call_participant_muted',
            'agent.voice.call_participant_unmuted',
            'agent.voice.call_participant_hold',
            'agent.voice.call_participant_unhold',
            'agent.voice.call_agent_invited',
            'agent.voice.call_agent_transfer',
            'agent.voice.call_user_joined',
            'agent.voice.call_agent_joined',
            'agent.voice.call_agent_cancel_invite',
            'agent.voice.call_agent_ignore_invite',
            'agent.voice.call_agent_left',
            'agent.voice.call_user_left',
            'agent.voice.call_user_disconnected',
            'agent.voice.call_agent_disconnected',
            'agent.voice.call_agent_hangup',
            'agent.voice.call_started',
            'agent.voice.call_ended',
        ];

        return $this->getPhrasesResponse($request, $phrases);
    }

    /**
     * @ApiDoc(
     *      section="Languages",
     *      description="provide admin phrases for frontend",
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      }
     * )
     * @Rest\Get("/admin_phrases")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function adminPhrasesAction(Request $request)
    {
        $phrases = [
            'agent.voice.call_new_incoming',
            'agent.voice.call_new_outgoing',
            'agent.voice.call_answered',
            'agent.voice.call_participant_muted',
            'agent.voice.call_participant_unmuted',
            'agent.voice.call_participant_hold',
            'agent.voice.call_participant_unhold',
            'agent.voice.call_agent_invited',
            'agent.voice.call_agent_transfer',
            'agent.voice.call_user_joined',
            'agent.voice.call_agent_joined',
            'agent.voice.call_agent_cancel_invite',
            'agent.voice.call_agent_ignore_invite',
            'agent.voice.call_agent_left',
            'agent.voice.call_user_left',
            'agent.voice.call_user_disconnected',
            'agent.voice.call_agent_disconnected',
            'agent.voice.call_agent_hangup',
            'agent.voice.call_started',
            'agent.voice.call_ended',
        ];

        return $this->getPhrasesResponse($request, $phrases);
    }

    /**
     * @param Request $request
     * @param array   $phrases
     *
     * @return JsonResponse
     */
    private function getPhrasesResponse(Request $request, array $phrases)
    {
        $translate = $this->container->get('deskpro.core.translate');
        $language  = $this->container->get('language_stack')->getActiveOrDefault();

        if ($request->get('language')) {
            $language = $this->getManager()->getRepository(Language::class)->find($request->get('language'));
        }

        $output = MapUtils::map($phrases, function ($idx, $id) use ($translate, $language) {
            return [$id, $translate->phrase($id, [], $language)];
        });

        $res = new JsonResponse($output);

        if ($request->getRequestFormat('json') === 'js') {
            $res->headers->set('Content-Type', 'application/javascript');
            $cb = $request->request->get('callback', 'DP_SET_PHRASES');
            $res->setCallback($cb);
        }

        // TODO proper caching headers here, phrases should reload:
        // - new version
        // - when langs are updated (need some global uid that changes when admin edits phrase)

        return $res;
    }
}
