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
        $data['chat']['formMessage']        = $preChatForm->getFormMessage();
        $data['widget']['greetingTitle']    = 'greeting';
        $data['proactive']['options']       = [
            'greetingTitle'    => 'proactive.greeting',
            'title'            => 'proactive.title',
            'description'      => 'proactive.description',
            'buttonText'       => 'proactive.button',
            'inputPlaceholder' => 'proactive.placeholder',
        ];

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
            'chat.save_ticket.question',
            'chat.save_ticket.intro',
            'chat.save_ticket.thanks',
            'chat.save_ticket.button_yes',
            'chat.save_ticket.button_no',
            'chat.agent_assigned.message',
            'message_user-joined',
            'message_user-left',
            'message_ended',
            'message_ended-by',
            'message_assigned',
            'message_unassigned',
            'chat.no_agent_online',
            'chat.ended',
            'chat.end_block.question_header',
            'chat.end_block.buttons.yes',
            'chat.end_block.buttons.no',
            'chat.create_ticket.header',
            'chat.create_ticket.intro',
            'chat.create_ticket.button',
            'chat.rating_block.question_header',
            'chat.rating_block.buttons.helpful',
            'chat.rating_block.buttons.unhelpful',
            'chat.rating_block.thank_you_header',
            'tickets.form.name',
            'tickets.form.email',
            'tickets.form.department',
            'tickets.form.message',
            'tickets.form.product',
            'tickets.form.priority',
            'tickets.form.category',
            'tickets.form.submit',
            'tickets.form.dragNDrop',
            'tickets.form.or',
            'tickets.form.chooseAFile',
            'tickets.form.chooseFiles',
            'tickets.form.select',
            'tickets.form.back',
            'tickets.form.required',
            'tickets.form.header',
            'chat.transcript_block.question_header',
            'chat.transcript_block.answer_header',
            'chat.transcript_block.yes_button',
            'chat.transcript_block.no_button',
            'chat.transcript_block.send_button',
            'blocks.continue_chat.link',
            'tickets.form.saving',
            'tickets.form.thanks_header',
            'tickets.form.thanks',
            'blocks.continue_chat.title',
            'blocks.tickets.title',
            'blocks.tickets.view_all_link',
            'chat.header.title',
            'chat.enter_form.button',
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
            return [$id, $translate->phrase(sprintf('helpcenter.messenger.%s', $id), [], $language) ?: "!$id!"];
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
