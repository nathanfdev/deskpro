<?php

namespace DeskPRO\Bundle\MessengerBundle\Controller;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\CustomDefChat;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutCollection;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\MessengerBundle\Service\MessengerSettingsResolver;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerTickets;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\PreChatForm;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\PreChatFormCustomField;
use DeskPRO\Component\Util\MapUtils;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Router;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ServiceController.
 *
 * @ApiModes("all")
 * @ApiUserContext("open")
 *
 * @Rest\Route("/service")
 * @Feature("messenger")
 */
class ServiceController extends AbstractMessengerController
{
    /**
     * You can use this endpoint to gather information about clients you need to obtain notifications and alerts.
     *
     * @ApiDoc(
     *     section="Messenger",
     *     resourceDescription="Service actions",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     requirements={
     *          {
     *              "name"="visitorId",
     *              "requirement"="[a-zA-Z0-9\\.\\-_]+",
     *              "description"="id of the visitor to look for",
     *              "dataType"="string"
     *          }
     *      },
     *     output="DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerSettings"
     * )
     * @Rest\Get("/setup")
     *
     * @return View
     */
    public function messengerSetupAction(Request $request)
    {
        $brand                     = $this->get('brand_stack')->getActive()->getBrand();
        $messengerSettingsResolver = $this->get('messenger.service.settings_resolver');
        $settings                  = $messengerSettingsResolver->getMessengerSettings($brand);
        $data                      = $this->get('serializer')->toArray($settings, new SideloadSerializationContext());

        $preChatForm     = $settings->getChat()->getPreChatForm();
        $ticketsSettings = $settings->getTickets();

        unset($data['embed']['jwtSecret']); // should use serializer views

        $data['chat']['preChatForm']        = $this->getPreChatFormConfig($preChatForm);
        $data['tickets']['formConfig']      = $this->getTicketFormConfig($ticketsSettings);
        $data['chat']['formMessageEnabled'] = $preChatForm->isFormMessageEnabled();

        $person   = $this->getUser();
        $language = $person && $person->getId()
            ? $person->getLanguage()->getId()
            : $this->container->get('language_stack')->getActiveOrDefault();

        $data['language'] = [
            'id'      => $language->getId(),
            'locale'  => $language->getLocale(),
            'version' => $messengerSettingsResolver->getSettings(MessengerSettingsResolver::WIDGET_LANG_VERSION),
        ];

        $data['tickets']['uploadTo'] = $data['chat']['uploadTo'] = $this->generateUrl(
            'messenger_blob_upload', [], UrlGeneratorInterface::ABSOLUTE_URL
        );

        $manifestPath = $this->container->get('templating.helper.assets')->getUrl('asset-manifest.json', 'messenger_assets');

        $data['bundleUrl'] = [
            'manifest'   => $manifestPath,
            'path'       => $this->container->get('templating.helper.assets')->getUrl('', 'messenger_assets'),
            'isDev'      => $this->get('settings_resolver')->getGlobalSettings()->get('messenger.is_dev', false),
            'isAbsolute' => $this->isAbsoluteUrl($manifestPath),
        ];

        return View::create($data, Response::HTTP_OK);
    }

    /**
     * You can use this endpoint to gather information about clients you need to obtain notifications and alerts.
     *
     * @ApiDoc(
     *     section="Messenger",
     *     resourceDescription="Service actions",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     }
     * )
     * @Rest\Get("/translation")
     *
     * @return View
     */
    public function getTranslationAction(Request $request)
    {
        $messengerSettingsResolver = $this->get('messenger.service.settings_resolver');
        $phrases                   = array_merge($messengerSettingsResolver->getEditablePhrases(), [
            'helpcenter.messenger.blocks_continue_chat_link',
            'helpcenter.messenger.blocks_continue_chat_title',
            'helpcenter.messenger.blocks_knowledgebase_title',
            'helpcenter.messenger.blocks_search_block_no_results',
            'helpcenter.messenger.blocks_search_block_search_label',
            'helpcenter.messenger.blocks_search_block_see_more_results',
            'helpcenter.messenger.blocks_start_chat_description',
            'helpcenter.messenger.blocks_start_chat_title',
            'helpcenter.messenger.blocks_start_chat_link',
            'helpcenter.messenger.blocks_tickets_title',
            'helpcenter.messenger.blocks_tickets_view_all_link',
            'helpcenter.messenger.chat_agent_assigned_message',
            'helpcenter.messenger.chat_attach_file',
            'helpcenter.messenger.chat_busy',
            'helpcenter.messenger.chat_create_ticket_button',
            'helpcenter.messenger.chat_create_ticket_header',
            'helpcenter.messenger.chat_create_ticket_intro',
            'helpcenter.messenger.chat_dragdrop_drag_and_drop',
            'helpcenter.messenger.chat_dragdrop_uploading',
            'helpcenter.messenger.chat_end_block_buttons_no',
            'helpcenter.messenger.chat_end_block_buttons_yes',
            'helpcenter.messenger.chat_end_block_question_header',
            'helpcenter.messenger.chat_end_chat',
            'helpcenter.messenger.chat_ended',
            'helpcenter.messenger.chat_enter_form_button',
            'helpcenter.messenger.chat_header_title',
            'helpcenter.messenger.chat_no_agent_online',
            'helpcenter.messenger.chat_pre_chat_form_form_message',
            'helpcenter.messenger.chat_prompt',
            'helpcenter.messenger.chat_rating_block_buttons_helpful',
            'helpcenter.messenger.chat_rating_block_buttons_unhelpful',
            'helpcenter.messenger.chat_rating_block_question_header',
            'helpcenter.messenger.chat_rating_block_thank_you_header',
            'helpcenter.messenger.chat_save_ticket_button_no',
            'helpcenter.messenger.chat_save_ticket_button_yes',
            'helpcenter.messenger.chat_save_ticket_intro',
            'helpcenter.messenger.chat_save_ticket_question',
            'helpcenter.messenger.chat_save_ticket_thanks',
            'helpcenter.messenger.chat_send_message',
            'helpcenter.messenger.chat_transcript_block_answer_header',
            'helpcenter.messenger.chat_transcript_block_no_button',
            'helpcenter.messenger.chat_transcript_block_question_header',
            'helpcenter.messenger.chat_transcript_block_send_button',
            'helpcenter.messenger.chat_transcript_block_yes_button',
            'helpcenter.messenger.loading',
            'helpcenter.messenger.message_agent_timeout',
            'helpcenter.messenger.message_assigned',
            'helpcenter.messenger.message_ended',
            'helpcenter.messenger.message_ended_by',
            'helpcenter.messenger.message_unassigned',
            'helpcenter.messenger.message_user_joined',
            'helpcenter.messenger.message_user_left',
            'helpcenter.messenger.powered_by',
            'helpcenter.messenger.tickets_form_add_attachment',
            'helpcenter.messenger.tickets_form_back',
            'helpcenter.general.category',
            'helpcenter.general.form_choose_file',
            'helpcenter.general.form_choose_files',
            'helpcenter.general.department',
            'helpcenter.general.drag_and_drop',
            'helpcenter.general.email_address',
            'helpcenter.messenger.tickets_form_header',
            'helpcenter.messenger.tickets_form_message',
            'helpcenter.messenger.tickets_form_name',
            'helpcenter.messenger.tickets_form_or',
            'helpcenter.messenger.tickets_form_priority',
            'helpcenter.messenger.tickets_form_product',
            'helpcenter.messenger.tickets_form_required',
            'helpcenter.messenger.tickets_form_saving',
            'helpcenter.messenger.tickets_form_select',
            'helpcenter.messenger.tickets_form_submit',
            'helpcenter.messenger.tickets_form_thanks',
            'helpcenter.messenger.tickets_form_thanks_header',
        ]);

        $translate = $this->container->get('deskpro.core.translate');
        $language  = null;

        /** @var Person $person */
        $person = $this->getUser();

        $language = $person && $person->getId()
            ? $person->getLanguage()
            : $this->container->get('language_stack')->getActiveOrDefault();

        //look for requested language only when user has no preferred one
        if ((!$person || !$person->getId()) && $request->get('language')) {
            $entityRepository = $this->getManager()->getRepository(Language::class);
            if (!$language = $entityRepository->find($request->get('language'))) {
                $language = $entityRepository->getForLangCode($request->get('language'));
            }
        }

        $output = MapUtils::map($phrases, function ($idx, $id) use ($translate, $language) {
            return [$id, $translate->phrase($id, [], $language) ?: "!$id!"];
        });

        return View::create($output, Response::HTTP_OK);
    }

    protected function isAbsoluteUrl($url)
    {
        return false !== strpos($url, '://') || '//' === substr($url, 0, 2);
    }

    /**
     * @param MessengerTickets $ticketsSettings
     *
     * @return array
     */
    private function getTicketFormConfig(MessengerTickets $ticketsSettings)
    {
        $em = $this->get('doctrine.orm.default_entity_manager');
        /** @var LayoutCollection $layouts */
        $layouts = $this->container->getTicketLayoutManager()->getUserLayouts(true);

        $customTicketFields = $em->getRepository(CustomDefTicket::class)->getTopFields();

        $ticketFormConfig = [];
        foreach ($layouts as $k => $layout) {
            /** @var Layout $layout */
            $layoutData           = ['department' => $k ?: 0];
            $layoutData['fields'] = [];
            foreach ($layout->all() as $f) {
                $ar              = $f->exportToArray();
                $ar['field_id']  = $f->getId();

                $ar['required'] = in_array($f->getFieldType(), ['department', 'person', 'subject', 'message'], true);

                if ($f->getFieldType() === 'department') {
                    $ar['is_hidden'] = $ticketsSettings->getDepartmentOption() === MessengerTickets::TICKET_DEPARTMENT_OPTION_HIDDEN;
                } elseif ($f->getId() === 'subject') {
                    $ar['is_hidden'] = $ticketsSettings->getSubjectOption() === MessengerTickets::TICKET_SUBJECT_OPTION_PRESET;
                } elseif ($f->getFieldType() === 'ticket_field') {
                    $ar['data'] = $this->get('serializer')->toArray($customTicketFields[$f->getFieldId()], new SideloadSerializationContext());
                    if (isset($ar['data']['required'])) {
                        $ar['required'] = $ar['data']['required'];
                    }
                }
                $layoutData['fields'][] = $ar;
            }

            $ticketFormConfig[] = $layoutData;
        }

        return $ticketFormConfig;
    }

    /**
     * @param PreChatForm $preChatForm
     *
     * @return array
     */
    private function getPreChatFormConfig(PreChatForm $preChatForm)
    {
        $em = $this->get('doctrine.orm.default_entity_manager');
        if ($preChatForm->isEnabled()) {
            /** @var CustomDefChat[] $fields */
            $fields = $em->getRepository(CustomDefChat::class)->getTopFields();
            $config = [
                'department' => 0,
                'fields'     => [],
            ];
            if ($preChatForm->isNameEnabled()) {
                array_push(
                    $config['fields'],
                    [
                        'field_type' => 'text',
                        'field_id'   => 'name',
                        'required'   => $preChatForm->isNameRequired(),
                        'data'       => ['title' => 'Name'],
                    ]
                );
            }
            if ($preChatForm->isEmailEnabled()) {
                array_push(
                    $config['fields'],
                    [
                        'field_type' => 'email',
                        'field_id'   => 'email',
                        'required'   => $preChatForm->isNameRequired(),
                        'data'       => ['title' => 'Email'],
                    ]
                );
            }
            array_push(
                $config['fields'],
                [
                    'field_type' => 'department',
                    'field_id'   => 'chat_department',
                    'is_hidden'  => !$preChatForm->isDepartmentSelectable(),
                    'required'   => true,
                ]
            );
            $preChatFields = $preChatForm->getFields()->toArray();
            usort($preChatFields, function ($a, $b) {
                /* @var PreChatFormCustomField $a */
                /* @var PreChatFormCustomField $b */
                return $a->getDisplayOrder() - $b->getDisplayOrder();
            });
            foreach ($preChatFields as $field) {
                /** @var PreChatFormCustomField $field */
                if (!isset($fields[$field->getId()]) || !$field->isEnabled()) {
                    continue;
                }
                $customField = $fields[$field->getId()];
                $ar          = [
                    'field_type' => 'chat_field',
                    'field_id'   => 'chat_field_'.$customField->getId(),
                    'data'       => $this->get('serializer')->toArray($customField, new SideloadSerializationContext()),
                ];
                if (isset($ar['data']['required'])) {
                    $ar['required'] = $ar['data']['required'];
                }

                array_push(
                    $config['fields'],
                    $ar
                );
            }

            return [$config];
        } else {
            return [];
        }
    }

    /**
     * @Router\Route("/blob", name="messenger_blob_upload")
     * @Router\Method("POST")
     *
     * @param Request $request
     * @param string  $restrictionSet
     *
     * @return JsonResponse
     */
    public function uploadBlobAction(Request $request, $restrictionSet = null)
    {
        $file = $request->files->get('file[blob]', null, true);
        if (!$file instanceof UploadedFile) {
            return new JsonResponse([
                'success' => false,
                'error'   => [
                    'code' => 'no_file_in_request',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $error = $this->get('attachment_accepter')->getError($file, $restrictionSet ? $restrictionSet.'.user' : 'user');
        if ($error) {
            $error_code = $error['error_code'];
            $params     = [];

            $error_detail = $error['error_detail'];
            if ($error_detail) {
                $params = ['detail' => $error_detail];
            }

            $phrase = sprintf('portal.forms.error_accept_%s', $error_code);

            return new JsonResponse([
                'success' => false,
                'error'   => [
                    'message' => $this->get('language_manager')->phrase($phrase, $params),
                    'code'    => $error_code,
                    'detail'  => $error_detail,
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $props = [];
        if ($request->query->get('tag', '')) {
            $props['tag'] = trim($request->query->get('tag', ''));
        }

        /** @var Blob $blob */
        $blob = $this->get('attachment_accepter')->accept($file, true, $props);

        return new JsonResponse([
            'success' => true,
            'blob'    => [
                'id'        => $blob->getId(),
                'filename'  => $blob->getFilename(),
                'authcode'  => $blob->getAuthcode(),
                'size'      => $blob->getReadableFilesize(),
                'icon_html' => '',
                'is_image'  => $blob->isImage(),
                'url'       => $blob->getDownloadUrl(true),
            ],
        ]);
    }
}
