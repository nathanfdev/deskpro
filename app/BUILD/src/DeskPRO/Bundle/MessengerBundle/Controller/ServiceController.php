<?php

namespace DeskPRO\Bundle\MessengerBundle\Controller;

use Application\DeskPRO\Entity\CustomDefTicket;
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

        $layouts = $this->container->getTicketLayoutManager()->getUserLayouts(true);

        $l = [];
        foreach ($layouts as $k => $layout) {
            $layout = array_merge(['department' => $k], $layout->exportToArray());
            foreach ($layout['fields'] as &$item) {
                if ($item['field_type'] === 'ticket_field') {
                    $item['data'] = $this->get('serializer')->toArray($this->getRepository(CustomDefTicket::class)
                        ->find($item['id']), new SideloadSerializationContext());
                }
            }
            $l[] = $layout;
        }

        $data['tickets']['formConfig'] = $l;

        $data['bundleUrl'] = [
            'manifest' => $this->container->get('templating.helper.assets')->getUrl('asset-manifest.json', 'messenger_assets'),
            'path'     => $this->container->get('templating.helper.assets')->getUrl('', 'messenger_assets'),
            'isDev'    => $this->get('settings_resolver')->getGlobalSettings()->get('messenger.is_dev', false),
        ];

        return View::create($data, Response::HTTP_OK);
    }
}
