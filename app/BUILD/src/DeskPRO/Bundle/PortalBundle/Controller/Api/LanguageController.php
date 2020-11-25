<?php

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
            'helpcenter.account.login_invalid',
            'helpcenter.account.logout_confirm',
            'helpcenter.account.profile_delete_picture',
            'helpcenter.community.filters',
            'helpcenter.community.my_activity',
            'helpcenter.community.reset_all_filters',
            'helpcenter.community.status',
            'helpcenter.duration_short.days',
            'helpcenter.duration_short.hours',
            'helpcenter.duration_short.minutes',
            'helpcenter.duration_short.months',
            'helpcenter.duration_short.seconds',
            'helpcenter.duration_short.weeks',
            'helpcenter.duration_short.years',
            'helpcenter.forms.date_picker_next_month',
            'helpcenter.forms.date_picker_previous_month',
            'helpcenter.forms.date_picker_time',
            'helpcenter.forms.label_drag_overlay',
            'helpcenter.forms.label_drag_overlay',
            'helpcenter.forms.label_reset',
            'helpcenter.general.add_comment',
            'helpcenter.general.authors_list',
            'helpcenter.general.back',
            'helpcenter.general.chats',
            'helpcenter.general.comment_btn_save',
            'helpcenter.general.comment_login_first',
            'helpcenter.general.comments_title',
            'helpcenter.general.copied',
            'helpcenter.general.copy_to_clipboard',
            'helpcenter.general.drag_and_drop',
            'helpcenter.general.files_type',
            'helpcenter.general.filter',
            'helpcenter.general.form_choose_file',
            'helpcenter.general.form_choose_files',
            'helpcenter.general.last_updated',
            'helpcenter.general.nav_community',
            'helpcenter.general.nav_guides',
            'helpcenter.general.nav_kb',
            'helpcenter.general.nav_news',
            'helpcenter.general.no_search_results_general',
            'helpcenter.general.or',
            'helpcenter.general.prop_comments_asc',
            'helpcenter.general.prop_comments_desc',
            'helpcenter.general.prop_date_asc',
            'helpcenter.general.prop_date_desc',
            'helpcenter.general.prop_popularity_asc',
            'helpcenter.general.prop_popularity_desc',
            'helpcenter.general.prop_rating_asc',
            'helpcenter.general.prop_rating_desc',
            'helpcenter.general.prop_views_asc',
            'helpcenter.general.prop_views_desc',
            'helpcenter.general.published',
            'helpcenter.general.remove',
            'helpcenter.general.select',
            'helpcenter.general.show_count_more',
            'helpcenter.general.show_x_more',
            'helpcenter.general.sort',
            'helpcenter.general.viewed_by_agents_only',
            'helpcenter.general.your_comment_label',
            'helpcenter.guides.default_description',
            'helpcenter.guides.default_description_short',
            'helpcenter.guides.in_section',
            'helpcenter.guides.next_page',
            'helpcenter.guides.no_matching_pages',
            'helpcenter.guides.previous_page',
            'helpcenter.guides.search_table_of_contents',
            'helpcenter.guides.start_reading',
            'helpcenter.guides.pages_in',
            'helpcenter.guides.page_sections',
            'helpcenter.guides.view_all_guides',
            'helpcenter.label.search',
            'helpcenter.search.view_all_results',
            'helpcenter.search.your_tickets',
            'helpcenter.tickets.related_articles_desc',
            'helpcenter.forms.confirm_reset',
            'helpcenter.general.show_less',
            'portal.account.login-btn',
            'portal.account.login-disabled',
            'portal.account.login-email',
            'portal.account.login-invalid',
            'portal.account.login-password',
            'portal.account.login-password-reminder',
            'portal.account.login-stay-logged-in',
            'portal.account.logout-confirm',
            'portal.account.profile-delete-picture',
            'portal.chat.agent_disconnected',
            'portal.chat.agent_typing_message',
            'portal.chat.asset_failed',
            'portal.chat.asset_not_delivered',
            'portal.chat.attached_photo',
            'portal.chat.cancel_end_chat',
            'portal.chat.chat_transcript',
            'portal.chat.check_validation_code',
            'portal.chat.details-placeholder',
            'portal.chat.dismiss_message',
            'portal.chat.dropzone1',
            'portal.chat.dropzone2',
            'portal.chat.end_chat',
            'portal.chat.end_chat_confirm_title',
            'portal.chat.expired_jwt_token',
            'portal.chat.feedback_action',
            'portal.chat.feedback_enter_message',
            'portal.chat.feedback_label',
            'portal.chat.feedback_not_helpful_title',
            'portal.chat.feedback_title',
            'portal.chat.find_agent_now',
            'portal.chat.find_another_agent',
            'portal.chat.helpful',
            'portal.chat.invalid_jwt_token',
            'portal.chat.label-department',
            'portal.chat.label-email',
            'portal.chat.label-name',
            'portal.chat.looking_for_another_agent',
            'portal.chat.message_type',
            'portal.chat.message_wait-long',
            'portal.chat.message_wait-pending',
            'portal.chat.message_wait-ticket',
            'portal.chat.missing_jwt_token',
            'portal.chat.mute_button',
            'portal.chat.not_helpful',
            'portal.chat.online_agent',
            'portal.chat.rate_agent_title',
            'portal.chat.reopen_chat',
            'portal.chat.reopen_chat_action',
            'portal.chat.reply_to',
            'portal.chat.require_validate_email',
            'portal.chat.screen_share',
            'portal.chat.see_full_image',
            'portal.chat.send_another_validation_email',
            'portal.chat.sent_validation_code',
            'portal.chat.start',
            'portal.chat.support_powered_by',
            'portal.chat.transcript_action',
            'portal.chat.transcript_already_sent',
            'portal.chat.transcript_desc',
            'portal.chat.transcript_title',
            'portal.chat.type_message_to',
            'portal.chat.upload_file',
            'portal.chat.user_is_blocked',
            'portal.chat.validation_email_was_sent',
            'portal.forms.confirm_reset',
            'portal.forms.error_upload_file',
            'portal.forms.error_upload_html_size',
            'portal.forms.error_upload_ini_size',
            'portal.forms.label_choose',
            'portal.forms.label_comment',
            'portal.forms.label_drag',
            'portal.forms.label_drag_overlay',
            'portal.forms.label_full_name',
            'portal.forms.label_reset',
            'portal.general.add-comment',
            'portal.general.agents-available',
            'portal.general.btn-download-pdf',
            'portal.general.btn-share',
            'portal.general.comment_btn_save',
            'portal.general.comments-title',
            'portal.general.delete',
            'portal.general.nav-chat',
            'portal.general.nav-community',
            'portal.general.nav-downloads',
            'portal.general.nav-guides',
            'portal.general.nav-kb',
            'portal.general.nav-news',
            'portal.general.nav-newticket',
            'portal.general.nav-tickets',
            'portal.general.no-agents-available',
            'portal.general.no-search-results-general',
            'portal.general.prop_comments_asc',
            'portal.general.prop_comments_desc',
            'portal.general.prop_date_asc',
            'portal.general.prop_date_desc',
            'portal.general.prop_popularity_asc',
            'portal.general.prop_popularity_desc',
            'portal.general.prop_rating_asc',
            'portal.general.prop_rating_desc',
            'portal.general.prop_views_asc',
            'portal.general.prop_views_desc',
            'portal.general.published',
            'portal.general.select_placeholder',
            'portal.general.select_search_placeholder',
            'portal.general.share-this',
            'portal.general.show_less',
            'portal.general.show_x_more',
            'portal.general.sort',
            'portal.general.start-chat',
            'portal.general.submit-community-topic',
            'portal.general.toggle_off',
            'portal.general.toggle_on',
            'portal.general.updated',
            'portal.general.your_comment_label',
            'portal.tickets.new-intro',
            'portal.tickets.new-title',
            'portal.tickets.related_articles_desc',
            'portal.tickets.related_articles_title',
            'portal.tickets.thank_you',
            'portal.tickets.thank_you_desc',
            'portal.widget.label_add_attachment',
            'portal.widget.new-ticket-title',
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
            if ($this->isHelpCenterTheme()) {
                return [$id, $translate->getPhraseText($id, $language)];
            }
            $phraseText = $translate->phrase($id, [], $language);
            if (!$phraseText && $translate->hasPhrasePlural($id, $language)) {
                $phraseText = $translate->getPhrasePluralTexts($id, $language);
            }

            return [$id, $this->convertToIcu($phraseText ?: "!$id!")];
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

    private function convertToIcu($phrase)
    {
        // plural categories case
        if (is_array($phrase)) {
            $res = '{count, plural,';
            foreach ($phrase as $cat => $phraseText) {
                $phraseText = str_replace('{{', '{', $phraseText);
                $phraseText = str_replace('}}', '}', $phraseText);
                $res .= "\n{$cat} {{$phraseText}}";
            }
            $res .= "\n}";

            return $res;
        }

        $phrase = str_replace('{{', '{', $phrase);
        $phrase = str_replace('}}', '}', $phrase);

        return $phrase;
    }
}
