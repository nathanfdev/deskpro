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

namespace DeskPRO\Bundle\PortalBundle\Controller\Api;

use Application\DeskPRO\Entity\Language;
use DeskPRO\Bundle\PortalBundle\Model\WidgetPhrases;
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
            'portal.general.btn-share',
            'portal.general.prop_date_desc',
            'portal.general.prop_date_asc',
            'portal.general.prop_views_desc',
            'portal.general.prop_views_asc',
            'portal.general.prop_rating_desc',
            'portal.general.prop_rating_asc',
            'portal.general.prop_popularity_desc',
            'portal.general.prop_popularity_asc',
            'portal.general.prop_comments_desc',
            'portal.general.prop_comments_asc',
            'portal.general.select_placeholder',
            'portal.general.select_search_placeholder',
            'portal.account.login-btn',
            'portal.account.login-email',
            'portal.account.login-invalid',
            'portal.account.login-disabled',
            'portal.account.login-password',
            'portal.account.login-password-reminder',
            'portal.account.login-stay-logged-in',
            'portal.account.logout-confirm',
            'portal.general.no-search-results-general',
            'portal.general.show_less',
            'portal.general.show_x_more',
            'portal.general.select_placeholder',
            'portal.general.delete',
            'portal.tickets.thank_you',
            'portal.tickets.thank_you_desc',
            'portal.tickets.new-title',
            'portal.tickets.new-intro',
            'portal.general.add-comment',
            'portal.general.comment_btn_save',
            'portal.general.comments-title',
            'portal.general.nav-newticket',
            'portal.general.nav-tickets',
            'portal.general.nav-kb',
            'portal.general.nav-downloads',
            'portal.general.nav-news',
            'portal.general.nav-feedback',
            'portal.general.nav-guides',
            'portal.general.nav-chat',
            'portal.general.published',
            'portal.general.submit-feedback',
            'portal.general.start-chat',
            'portal.general.agents-available',
            'portal.general.no-agents-available',
            'portal.general.share-this',
            'portal.general.toggle_on',
            'portal.general.toggle_off',
            'portal.general.updated',
            'portal.forms.error_upload_file',
            'portal.forms.error_upload_html_size',
            'portal.forms.error_upload_ini_size',
            'portal.forms.label_comment',
            'portal.forms.label_choose',
            'portal.forms.label_drag',
            'portal.forms.label_drag_overlay',
            'portal.forms.label_full_name',
            'portal.forms.label_reset',
            'portal.forms.confirm_reset',
            'portal.chat.asset_failed',
            'portal.chat.asset_not_delivered',
            'portal.chat.cancel_end_chat',
            'portal.chat.chat_transcript',
            'portal.chat.details-placeholder',
            'portal.chat.dismiss_message',
            'portal.chat.dropzone1',
            'portal.chat.dropzone2',
            'portal.chat.end_chat',
            'portal.chat.end_chat_confirm_title',
            'portal.chat.feedback_enter_message',
            'portal.chat.feedback_action',
            'portal.chat.feedback_label',
            'portal.chat.feedback_not_helpful_title',
            'portal.chat.feedback_title',
            'portal.chat.helpful',
            'portal.chat.not_helpful',
            'portal.chat.label-name',
            'portal.chat.label-email',
            'portal.chat.label-department',
            'portal.chat.message_type',
            'portal.chat.message_wait-pending',
            'portal.chat.message_wait-long',
            'portal.chat.message_wait-ticket',
            'portal.chat.mute_button',
            'portal.chat.online_agent',
            'portal.chat.rate_agent_title',
            'portal.chat.reopen_chat',
            'portal.chat.reopen_chat_action',
            'portal.chat.reply_to',
            'portal.chat.screen_share',
            'portal.chat.start',
            'portal.chat.support_powered_by',
            'portal.chat.transcript_desc',
            'portal.chat.transcript_title',
            'portal.chat.transcript_action',
            'portal.chat.type_message_to',
            'portal.chat.agent_typing_message',
            'portal.chat.upload_file',
            'portal.chat.transcript_already_sent',
            'portal.chat.require_validate_email',
            'portal.chat.sent_validation_code',
            'portal.chat.check_validation_code',
            'portal.chat.send_another_validation_email',
            'portal.chat.validation_email_was_sent',
            'portal.chat.agent_disconnected',
            'portal.chat.find_another_agent',
            'portal.chat.find_agent_now',
            'portal.chat.looking_for_another_agent',
            'portal.chat.attached_photo',
            'portal.chat.see_full_image',
            'portal.chat.user_is_blocked',
            'portal.chat.missing_jwt_token',
            'portal.chat.invalid_jwt_token',
            'portal.chat.expired_jwt_token',
            'portal.widget.new-ticket-title',
            'portal.widget.label_add_attachment',
            'portal.widget.online_agents',
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
        $translate = $this->container->get('deskpro.core.translate');
        $language  = $this->container->get('language_stack')->getActiveOrDefault();

        $languageId = $request->query->getInt('language');
        if ($languageId) {
            $language = $this->getManager()->getRepository(Language::class)->find($languageId);
        }

        $translatedPhrases = MapUtils::map($phrases, function ($idx, $id) use ($translate, $language) {
            return [$id, $translate->phrase($id, [], $language)];
        });

        $serialized = $this->get('serializer')->toArray(new WidgetPhrases($translatedPhrases, $language));
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
