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

use DeskPRO\Component\Util\MapUtils;
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
        $phrases = [
            'portal.tickets.related_articles_title',
            'portal.tickets.related_articles_desc',
            'portal.general.prop_date',
            'portal.general.prop_views',
            'portal.general.prop_rating',
            'portal.general.prop_popularity',
            'portal.general.prop_comments',
            'portal.general.select_placeholder',
            'portal.account.login-email',
            'portal.account.login-invalid',
            'portal.account.login-password',
            'portal.account.login-password-reminder',
            'portal.general.no-search-results-general',
            'portal.general.show_x_more',
            'portal.general.select_placeholder',
            'portal.general.delete',
            'portal.tickets.thank_you',
            'portal.tickets.thank_you_desc',
            'portal.tickets.new-title',
            'portal.tickets.new-intro',
            'portal.general.start-chat',
            'portal.forms.label_reset',
            'portal.forms.label_drag',
            'portal.forms.label_choose',
            'portal.chat.dismiss_message',
            'portal.chat.reply_to',
        ];

        return $this->getResponse($request, $phrases);
    }

    /**
     * @Route("/portal/api/lang/widget-chat-phrases.{_format}", name="portal_api_lang_widget_chat_phrases", requirements={"_format":"json|js"})
     * @Method({"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function widgetChatPhrasesAction(Request $request)
    {
        $phrases = [
            'user.chat.email',
            'user.chat.ended-no-agent',
            'user.chat.error',
            'user.chat.form_chat_button-submit',
            'user.chat.form_chat_send-file',
            'user.chat.form_create_button-submit',
            'user.chat.form_create_department',
            'user.chat.form_create_title',
            'user.chat.form_feedback_button-submit',
            'user.chat.form_feedback_comments',
            'user.chat.form_feedback_rate-satisfaction',
            'user.chat.form_feedback_rate-satisfied',
            'user.chat.form_feedback_rate-time',
            'user.chat.form_feedback_rate-unsatisfied',
            'user.chat.form_feedback_title',
            'user.chat.form_feedback_transcript-email',
            'user.chat.log-title',
            'user.chat.log_chat-id',
            'user.chat.log_created-date',
            'user.chat.log_fields_agent',
            'user.chat.log_fields_department',
            'user.chat.log_message_author-you',
            'user.chat.log_nav-view-chats',
            'user.chat.log_no_department',
            'user.chat.log_unassigned',
            'user.chat.message_agent-timeout',
            'user.chat.message_assigned',
            'user.chat.message_chatting-with',
            'user.chat.message_ended',
            'user.chat.message_ended-by',
            'user.chat.message_ended-by-user',
            'user.chat.message_finding-agent',
            'user.chat.message_long-wait',
            'user.chat.message_set-department',
            'user.chat.message_started',
            'user.chat.message_unassigned',
            'user.chat.message_uploading',
            'user.chat.message_user-joined',
            'user.chat.message_user-left',
            'user.chat.message_user-returned',
            'user.chat.message_user-timeout',
            'user.chat.message_wait',
            'user.chat.message_wait-timeout',
            'user.chat.name',
            'user.chat.submit-ticket-button',
            'user.chat.submit-ticket-title',
            'user.chat.transcript_sent',
            'user.chat.window_cancel',
            'user.chat.window_cancel-confirm',
            'user.chat.window_close',
            'user.chat.window_close_only',
            'user.chat.window_end-chat',
            'user.chat.window_offline-button',
            'user.chat.window_open-new',
            'user.chat.window_resume-button',
            'user.chat.window_start-button',
            'user.chat.window_upload-drag',
        ];

        return $this->getResponse($request, $phrases);
    }

    /**
     * @param Request $request
     * @param array   $phrases
     *
     * @return JsonResponse
     */
    protected function getResponse(Request $request, array $phrases)
    {
        $tr = $this->container->get('deskpro.core.translate');
        $language = $this->container->get('language_stack')->getActiveOrDefault();

        $output = MapUtils::map($phrases, function ($idx, $id) use ($tr, $language) {
            return [$id, $tr->phrase($id, [], $language)];
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
