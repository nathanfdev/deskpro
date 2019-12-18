<?php

namespace DeskPRO\Bundle\MessengerBundle\Controller;

use Application\DeskPRO\Entity\CustomDefChat;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutCollection;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        $data     = $this->get('serializer')->toArray($settings,  new SideloadSerializationContext());
        $em       = $this->get('doctrine.orm.default_entity_manager');

        /** @var LayoutCollection $layouts */
        $layouts = $this->container->getTicketLayoutManager()->getUserLayouts(true);

        $customTicketFields = $em->getRepository(CustomDefTicket::class)->getTopFields();

        $ticketFormConfig = [];
        foreach ($layouts as $k => $layout) {
            /** @var Layout $layout */
            $layoutData           = ['department' => $k ?: 0];
            $layoutData['fields'] = [];
            foreach ($layout->all() as $f) {
                if ($f->getFieldType() === 'attachments') {
                    continue;
                }
                $ar             = $f->exportToArray();
                $ar['field_id'] = $f->getId();
                if ($f->getFieldType() === 'ticket_field') {
                    $ar['data'] = $this->get('serializer')->toArray($customTicketFields[$f->getFieldId()], new SideloadSerializationContext());
                }
                $layoutData['fields'][] = $ar;
            }

            $ticketFormConfig[] = $layoutData;
        }

        $preChatForm = $settings->getChat()->getPreChatForm();
        if ($preChatForm->isEnabled()) {
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
            foreach ($preChatForm->getFields() as $field) {
                if (!isset($fields[$field->getId()])) {
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

            $data['chat']['preChatForm'] = [$config];
        }

        $data['tickets']['formConfig'] = $ticketFormConfig;

        $data['bundleUrl'] = [
            'manifest' => $this->container->get('templating.helper.assets')->getUrl('asset-manifest.json', 'messenger_assets'),
            'path'     => $this->container->get('templating.helper.assets')->getUrl('', 'messenger_assets'),
            'isDev'    => $this->get('settings_resolver')->getGlobalSettings()->get('messenger.is_dev', false),
        ];

        return View::create($data, Response::HTTP_OK);
    }
}
