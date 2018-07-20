<?php

namespace DeskPRO\Bundle\ApiBundle\Controller;

use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Phrase;
use Application\DeskPRO\Translate\Translate;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomPhraseType;
use DeskPRO\Bundle\AppBundle\Form\Type\TranslationType;
use DeskPRO\Component\Util\MapUtils;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\FormError;
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
    public static $exposeOnly = ['list', 'get', 'count'];

    /**
     * @ApiDoc(
     *     section="Languages",
     *     description="provide agent phrases for frontend",
     *     statusCodes={
     *         201="Created",
     *         400="Bad Request"
     *     },
     *     output="array"
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
            'agent.chrome.btn_recent',
            'agent.chrome.link_help',
            'agent.chrome.link_logout',
            'agent.chrome.link_preferences',
            'agent.chrome.nav_search',
            'agent.chrome.nav_agentchat',
            'agent.chrome.notification_tooltip',
            'agent.chrome.recent_tooltip',
            'agent.chrome.user_tooltip',
            'agent.chrome.view_tooltip',
            'agent.follow_up.add_follow_up',
            'agent.follow_up.cancel_if_reply',
            'agent.follow_up.follow_up_time',
            'agent.follow_up.follow_up_actions',
            'agent.follow_up.new_follow_up',
            'agent.follow_up.no_follow_ups',
            'agent.follow_up.you_must_add_one_action',
            'agent.follow_up.you_must_select_time',
            'agent.follow_up.error_agent',
            'agent.follow_up.error_agent_team',
            'agent.follow_up.error_reply',
            'agent.follow_up.error_note',
            'agent.follow_up.error_hold',
            'agent.follow_up.error_status',
            'agent.follow_up.error_macro',
            'agent.general.actions',
            'agent.general.add_a_label',
            'agent.general.add_action_term',
            'agent.general.admin',
            'agent.general.agent',
            'agent.general.agents',
            'agent.general.agent_email_address',
            'agent.general.agent_team',
            'agent.general.all',
            'agent.general.article',
            'agent.general.are_you_sure',
            'agent.general.assign_to_agent',
            'agent.general.attach_files',
            'agent.general.badge_legacy',
            'agent.general.badge_new',
            'agent.general.billing',
            'agent.general.brand',
            'agent.general.cancel',
            'agent.general.cancelled',
            'agent.general.category',
            'agent.general.chat',
            'agent.general.chats',
            'agent.general.chat_departments',
            'agent.general.clear',
            'agent.general.close_lc',
            'agent.general.changelog',
            'agent.general.comparison',
            'agent.general.create',
            'agent.general.criteria',
            'agent.general.crm',
            'agent.general.date_created',
            'agent.general.date_resolved',
            'agent.general.day',
            'agent.general.days',
            'agent.general.delete',
            'agent.general.department',
            'agent.general.departments',
            'agent.general.department.parent',
            'agent.general.display_options',
            'agent.general.done',
            'agent.general.download',
            'agent.general.draft',
            'agent.general.email_address',
            'agent.general.everyone',
            'agent.general.export',
            'agent.general.feedback',
            'agent.general.field',
            'agent.general.filter',
            'agent.general.file',
            'agent.general.filesize',
            'agent.general.filetype',
            'agent.general.first_name',
            'agent.general.follow_up',
            'agent.general.follow_ups',
            'agent.general.followers',
            'agent.general.global',
            'agent.general.groups',
            'agent.general.height',
            'agent.general.hold',
            'agent.general.hour',
            'agent.general.hours',
            'agent.general.id',
            'agent.general.insert',
            'agent.general.just_me',
            'agent.general.labels',
            'agent.general.language',
            'agent.general.languages',
            'agent.general.last_name',
            'agent.general.loading_dot',
            'agent.general.macro',
            'agent.general.mass_actions',
            'agent.general.me',
            'agent.general.merge',
            'agent.general.minutes',
            'agent.general.months',
            'agent.general.myself',
            'agent.general.name',
            'agent.general.new',
            'agent.general.news_post',
            'agent.general.no',
            'agent.general.none',
            'agent.general.note',
            'agent.general.off',
            'agent.general.on',
            'agent.general.or_sep',
            'agent.general.organization',
            'agent.general.org_position',
            'agent.general.pending',
            'agent.general.person',
            'agent.general.please_select',
            'agent.general.portal',
            'agent.general.priority',
            'agent.general.product',
            'agent.general.publish',
            'agent.general.ref',
            'agent.general.reply',
            'agent.general.reports',
            'agent.general.run_macro',
            'agent.general.save',
            'agent.general.saved',
            'agent.general.search',
            'agent.general.search_results',
            'agent.general.select',
            'agent.general.select_all',
            'agent.general.show',
            'agent.general.sla',
            'agent.general.sla_status',
            'agent.general.snippet',
            'agent.general.snippets',
            'agent.general.subject',
            'agent.general.status',
            'agent.general.task',
            'agent.general.tasks',
            'agent.general.team',
            'agent.general.teams',
            'agent.general.ticket',
            'agent.general.tickets',
            'agent.general.ticket_departments',
            'agent.general.title',
            'agent.general.topic',
            'agent.general.type',
            'agent.general.types',
            'agent.general.unassign',
            'agent.general.upload_image',
            'agent.general.urgency',
            'agent.general.url',
            'agent.general.user',
            'agent.general.when',
            'agent.general.width',
            'agent.general.workflow',
            'agent.general.yes',
            'agent.general.your_profile',
            'agent.grouping_option.status',
            'agent.grouping_option.agent',
            'agent.grouping_option.agent_team',
            'agent.grouping_option.date_created',
            'agent.grouping_option.department',
            'agent.grouping_option.language',
            'agent.grouping_option.urgency',
            'agent.guides.add_image',
            'agent.guides.alternative_text',
            'agent.guides.content_link',
            'agent.guides.images_drop_info',
            'agent.guides.select_a_content',
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
            'agent.onboarding.new_im_position_title',
            'agent.onboarding.new_im_position_text',
            'agent.onboarding.new_im_start_new_title',
            'agent.onboarding.new_im_start_new_text',
            'agent.search.type_ticket',
            'agent.search.no_results_found',
            'agent.snippets.all_departments',
            'agent.snippets.all_snippets',
            'agent.snippets.all_drafts',
            'agent.snippets.at_least_one_type',
            'agent.snippets.content_change',
            'agent.snippets.draft_status',
            'agent.snippets.edit_snippet',
            'agent.snippets.feedback',
            'agent.snippets.helpdesk_default',
            'agent.snippets.mass_actions_progress',
            'agent.snippets.my_drafts',
            'agent.snippets.my_snippets',
            'agent.snippets.my_team_snippets',
            'agent.snippets.my_teams_snippets',
            'agent.snippets.new_snippet',
            'agent.snippets.no_results_new_label',
            'agent.snippets.no_comment',
            'agent.snippets.ownership',
            'agent.snippets.please_select',
            'agent.snippets.revert_content',
            'agent.snippets.save_changes',
            'agent.snippets.set_as_draft',
            'agent.snippets.set_as_published',
            'agent.snippets.shortcut_code',
            'agent.snippets.show_only_with_feedback',
            'agent.snippets.snippet_created',
            'agent.snippets.snippet_is_draft',
            'agent.snippets.specific_departments',
            'agent.snippets.specific_teams',
            'agent.snippets.split_snippet',
            'agent.snippets.usage_history',
            'agent.snippets.used',
            'agent.snippets.variables',
            'agent.snippets.visibility',
            'agent.snippets.your_language',
            'agent.tickets.add_reply_action',
            'agent.tickets.add_note_action',
            'agent.tickets.assign_agent',
            'agent.tickets.assign_team',
            'agent.tickets.count_agents',
            'agent.tickets.hold_btn',
            'agent.tickets.put_on_hold',
            'agent.tickets.run_macro_action',
            'agent.tickets.status_archived',
            'agent.tickets.status_awaiting_agent',
            'agent.tickets.status_awaiting_user',
            'agent.tickets.status_resolved',
            'agent.tickets.unhold_btn',
            'agent.tickets.unhold_ticket',
            'agent.tickets.view_files',
            'agent.voice.incoming_call_title',
            'agent.voice.outgoing_call_title',
            'agent.voice.call_new_incoming',
            'agent.voice.call_new_outgoing',
            'agent.voice.call_auto_attendant_press_key',
            'agent.voice.call_auto_attendant_press_unsupported_key',
            'agent.voice.call_auto_attendant_press_repeat_key',
            'agent.voice.call_auto_attendant_press_extension_key',
            'agent.voice.call_auto_attendant_extension',
            'agent.voice.call_target',
            'agent.voice.call_rejected',
            'agent.voice.call_answered',
            'agent.voice.call_forward_answered',
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
     *     section="Languages",
     *     description="provide admin phrases for frontend",
     *     statusCodes={
     *         201="Created",
     *         400="Bad Request"
     *     },
     *     noOutput="array"
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
            'agent.voice.call_auto_attendant_press_key',
            'agent.voice.call_auto_attendant_press_unsupported_key',
            'agent.voice.call_auto_attendant_press_repeat_key',
            'agent.voice.call_auto_attendant_press_extension_key',
            'agent.voice.call_auto_attendant_extension',
            'agent.voice.call_target',
            'agent.voice.call_rejected',
            'agent.voice.call_answered',
            'agent.voice.call_forward_answered',
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
     *      description="provide agent phrases for frontend",
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *     output="array"
     * )
     * @Rest\Get("/email_phrases/{group}/{languageId}")
     * @Feature("email_templates")
     *
     * @param $languageId
     *
     * @return View
     */
    public function emailPhrasesAction($group, $languageId)
    {
        /** @var Translate $translate */
        $translate = $this->container->get('deskpro.core.translate');

        if (is_numeric($languageId)) {
            $language = $languageId;
        } else {
            $language = $this->getManager()->getRepository(Language::class)->findOneBy(['locale' => $languageId]);
        }

        $phrases = [];
        switch ($group) {
            case 'user':
                $phrases = [
                    'portal.email_subjects.*',
                    'user.email_subjects.*',
                    'portal.emails.*',
                    'user.emails.*',
                    'portal.general.*',
                    'user.general.*',
                    'portal.error.*',
                    'user.error.*',
                    'portal.tickets.*',
                    'user.tickets.*',
                    'portal.account.*',
                    'portal.articles.*',
                    'portal.chat.*',
                    'user.chat.*',
                    'user.defaults.*',
                    'portal.downloads.*',
                    'user.downloads.*',
                    'portal.feedback.*',
                    'user.feedback.*',
                    'portal.flashes.*',
                    'portal.forms.*',
                    'user.knowledgebase.*',
                    'user.lang.*',
                    'portal.news.*',
                    'user.news.*',
                    'user.profile.*',
                    'portal.sidebar.*',
                    'user.time.*',
                    'custom.emails.*',
                ];
                break;
            case 'agent':
                $phrases = [
                    'agent.email_subjects.*',
                    'agent.emails.*',
                    'agent.general.*',
                    'agent.error.*',
                    'agent.tickets.*',
                    'agent.account.*',
                    'agent.articles.*',
                    'agent.chat.*',
                    'agent.chrome.*',
                    'agent.downloads.*',
                    'agent.feedback.*',
                    'agent.flashes.*',
                    'agent.forms.*',
                    'agent.news.*',
                    'agent.sidebar.*',
                ];
                break;
        }

        $phrases = $translate->getArrayPhraseTexts($phrases, $language);

        return new View($phrases);
    }

    /**
     * @ApiDoc(
     *      section="Languages",
     *      description="provide translation of a phrase",
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *     output="array"
     * )
     * @Rest\Get("/translations/{phraseName}")
     * @Feature("email_templates")
     *
     * @param $phraseName
     *
     * @return View
     */
    public function getTranslationsAction($phraseName)
    {
        /** @var Translate $translate */
        $translate = $this->container->get('deskpro.core.translate');

        $languages    = $this->getManager()->getRepository(Language::class)->findAll();
        $translations = [];
        foreach ($languages as $language) {
            $translations[$language->getLocale()] = $translate->getPhraseText($phraseName, $language, true);
        }

        return new View($translations);
    }

    /**
     * @ApiDoc(
     *      section="Languages",
     *      description="provide translation of a phrase",
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *     input={
     *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\TranslationType",
     *     },
     *     output="array"
     * )
     * @Rest\Post("/translations/{phraseName}")
     * @Feature("email_templates")
     *
     * @param Request $request
     * @param $phraseName
     *
     * @return View
     */
    public function postTranslationsAction(Request $request, $phraseName)
    {
        $form = $this->createForm(TranslationType::class);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $translations = $form->getData()['translations'];
        /** @var Translate $translate */
        $translate = $this->container->get('deskpro.core.translate');

        foreach ($translations as $locale => $translation) {
            $language    = $this->getManager()->getRepository(Language::class)->findOneBy(['locale' => $locale]);
            $translation = trim($translation);
            if ($translation != $translate->getPhraseText($phraseName, $language, true)) {
                $phrase = $this->getManager()->getRepository(Phrase::class)
                    ->getPhraseForLanguage($phraseName, $language);
                if (!$translation) {
                    if ($phrase) {
                        $this->getManager()->remove($phrase);
                    }
                } else {
                    if (!$phrase) {
                        $phrase = new Phrase();
                        $phrase->setLanguage($language);
                        $phrase->setName($phraseName);
                        $phrase->setOriginalPhrase('');
                        $phrase->setOriginalHash(md5(null));
                    }

                    if (!$phrase->getOriginalPhrase()) {
                        $phrase->setOriginalPhrase('');
                        $phrase->setOriginalHash(md5(null));
                    }
                    $phrase->setPhrase($translation);
                    $this->getManager()->persist($phrase);
                }
            }
        }
        $this->getManager()->flush();

        return new View('OK');
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

        $format = 'twig';
        if ($request->get('format')) {
            $format = $request->get('format');
        }

        $output = MapUtils::map($phrases, function ($idx, $id) use ($translate, $language, $format) {
            switch ($format) {
                case 'icu':
                    $phraseText = $translate->phrase($id, [], $language);
                    if (!$phraseText && $translate->hasPhrasePlural($id, $language)) {
                        $phraseText = $translate->getPhrasePluralTexts($id, $language);
                    }

                    return [$id, $this->convertToIcu($phraseText ?: "!$id!")];
                case 'twig':
                default:
                    return [$id, $translate->phrase($id, [], $language) ?: "!$id!"];
            }
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

    /**
     * @ApiDoc(
     *      section="Languages",
     *      description="Create new custom phrase in several languages",
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *     input={
     *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\CustomPhraseType",
     *     },
     *     output="Application\DeskPRO\Entity\Phrase",
     * )
     * @Rest\Post("/custom_phrase")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function postCustomPhraseAction(Request $request)
    {
        $form = $this->createForm(CustomPhraseType::class);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $data = $form->getData();

        $phraseName = 'custom.emails.'.$data['name'];

        if ($this->getManager()->getRepository(Phrase::class)->findOneBy(['name' => $phraseName])) {
            $form->get('name')->addError(new FormError('Duplicate entry.'));
            throw new InvalidFormException($form);
        }

        $entityManager = $this->getManager();

        $phrases = [];

        /** @var Language $language */
        foreach ($this->getContainer()->get('language_manager')->getEnabledLanguages() as $language) {
            if (!empty($data['phrase_'.$language->getLocale()])) {
                $phrase = new Phrase();
                $phrase->setName($phraseName);
                $phrase->setLanguage($language);
                $phrase->setPhrase($data['phrase_'.$language->getLocale()]);
                $phrase->setOriginalHash('');
                $entityManager->persist($phrase);
                $phrases[] = $phrase;
            }
        }
        $entityManager->flush();

        return new JsonResponse($phrases);
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
