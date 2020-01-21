<?php

namespace DeskPRO\Bundle\MessengerBundle\Controller;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\CustomDefChat;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutCollection;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\PreChatForm;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\PreChatFormCustomField;
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
        $brand    = $this->get('brand_stack')->getActive()->getBrand();
        $settings = $this->get('messenger.service.settings_resolver')->getMessengerSettings($brand);
        $data     = $this->get('serializer')->toArray($settings, new SideloadSerializationContext());

        $preChatForm = $settings->getChat()->getPreChatForm();

        $data['chat']['preChatForm']         = $this->getPreChatFormConfig($preChatForm);
        $data['tickets']['formConfig']       = $this->getTicketFormConfig();
        $data['chat']['brandMessageEnabled'] = $preChatForm->isBrandMessageEnabled();
        $data['chat']['brandMessage']        = $preChatForm->getBrandMessage();

        $data['tickets']['uploadTo'] = $data['chat']['uploadTo'] = $this->generateUrl(
            'messenger_blob_upload', [], UrlGeneratorInterface::ABSOLUTE_URL
        );

        $data['bundleUrl'] = [
            'manifest' => $this->container->get('templating.helper.assets')->getUrl('asset-manifest.json', 'messenger_assets'),
            'path'     => $this->container->get('templating.helper.assets')->getUrl('', 'messenger_assets'),
            'isDev'    => $this->get('settings_resolver')->getGlobalSettings()->get('messenger.is_dev', false),
        ];

        return View::create($data, Response::HTTP_OK);
    }

    /**
     * @return array
     */
    private function getTicketFormConfig()
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
                $ar             = $f->exportToArray();
                $ar['field_id'] = $f->getId();
                if ($f->getFieldType() === 'ticket_field') {
                    $ar['data'] = $this->get('serializer')->toArray($customTicketFields[$f->getFieldId()], new SideloadSerializationContext());
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
                    ]
                );
            }
            if ($preChatForm->isDepartmentSelectable()) {
                array_push(
                    $config['fields'],
                    [
                        'field_type' => 'email',
                        'field_id'   => 'email',
                        'required'   => $preChatForm->isNameRequired(),
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
                array_push(
                    $config['fields'],
                    [
                        'field_type' => 'chat_field',
                        'field_id'   => 'chat_field_'.$customField->getId(),
                        'data'       => $this->get('serializer')->toArray($customField, new SideloadSerializationContext()),
                    ]
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
