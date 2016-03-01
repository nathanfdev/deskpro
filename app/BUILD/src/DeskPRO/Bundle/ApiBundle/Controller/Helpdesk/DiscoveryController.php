<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Controller\Helpdesk;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Model\PrimitiveArray;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Orb\Util\Arrays;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DiscoveryController.
 *
 * @ApiModes("all")
 */
class DiscoveryController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="Used by apps to detect that this is a real helpdesk",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/helpdesk/discover", name="api_helpdesk_discover")
     */
    public function discoverAction(Request $request)
    {
        //TODO use a model
        //TODO use brand stack
        //$brand = $this->get('brand_stack')->getActive();
        //$helpdesk_url = rtrim($brand->getSetting('core.deskpro_url'), '/') . '/';

        $s            = $this->get('deskpro.core.settings');
        $helpdesk_url = rtrim($s->get('core.deskpro_url'), '/').'/';

        $base_api_url = $helpdesk_url.'api/v2/';

        $ret = [
            'is_deskpro'   => true,
            'helpdesk_url' => $helpdesk_url,
            'base_api_url' => $base_api_url,
            'build'        => DP_BUILD_TIME,
        ];

        return View::create(
            $this->dataSerialize(new PrimitiveArray($ret)),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *     description="Used by apps when they need to know general information about a helpdesk such as which features are enabled",
     *     statusCodes={200="Success"}
     * )
     * @Get("/helpdesk/agent-client/info", name="api_helpdesk_agent_client_info")
     */
    public function agentClientInfoAction()
    {
        //TODO use a model/transformer

        /** @var \Application\DeskPRO\DependencyInjection\DeskproContainer $container */
        $container = $this->container;

        /** @var \Application\DeskPRO\Settings\Settings $settings */
        $settings = $container->get('deskpro.core.settings');

        /** @var \Application\DeskPRO\CustomFields\TicketFieldManager $field_manager */
        $field_manager = $this->container->getSystemService('ticket_fields_manager');

        /** @var \Application\DeskPRO\Entity\Person $me */
        $me = $this->getUser();
        $me->loadHelper('Agent');
        $me->loadHelper('AgentTeam');
        $me->loadHelper('AgentPermissions');
        $me->loadHelper('PermissionsManager');

        $data = [];

        $data['settings'] = [
            'multi_lang'    => $settings->get('core.enable_languages'),
            'helpdesk_name' => $settings->get('core.deskpro_name'),
            'attachments'   => [
                'agents' => [
                    'max_size'  => $settings->get('core.attach_agent_maxsize'),
                    'whitelist' => Arrays::removeEmptyString(explode(',', $settings->get('core.attach_agent_must_exts') ?: '')) ?: null,
                    'blacklist' => Arrays::removeEmptyString(explode(',', $settings->get('core.attach_agent_not_exts') ?: '')) ?: null,
                ],
            ],
        ];

        // TODO is there a repository somewhere this comes from?
        // See also DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/Nav/FilterEditPopupContainer.js
        $group_fields = [
            ['type' => 'department'],
            ['type' => 'organization'],
            ['type' => 'person'],
            ['type' => 'language'],
            ['type' => 'urgency'],
            ['type' => 'agent'],
            ['type' => 'agent_team'],
            ['type' => 'waiting_time'],
            ['type' => 'all_waiting_time'],
            ['type' => 'open_time'],
        ];

        $order_fields = [
            ['type' => 'urgency'],
            ['type' => 'date_created'],
            ['type' => 'date_last_agent_reply'],
            ['type' => 'date_last_user_reply'],
            ['type' => 'date_last_reply'],
            ['type' => 'date_user_waiting'],
            ['type' => 'total_user_waiting'],
        ];

        $group_fields = array_map(function ($v) {
            Arrays::unshiftAssoc($v, 'id', $v['type']);

            return $v;
        }, $group_fields);

        $order_fields = array_map(function ($v) {
            Arrays::unshiftAssoc($v, 'id', $v['type']);

            return $v;
        }, $order_fields);

        foreach ($field_manager->getFields() as $f) {
            $group_fields[] = [
                'id'       => 'ticket_field.'.$f->getId(),
                'type'     => 'ticket_field',
                'field_id' => $f->getId(),
            ];
        }

        $data['tickets'] = [
            'enabled'    => $me->hasPerm('agent_tickets.use'),
            'ref_code'   => $settings->get('core_tickets.use_ref'),
            'archiving'  => $settings->get('core_tickets.use_archive'),
            'field_info' => [
                'product' => [
                    'enabled'    => $field_manager->isProductEnabled(),
                    'default_id' => $settings->get('core.default_prod_id') ?: null,
                ],
                'category' => [
                    'enabled'    => $field_manager->isCategoryEnabled(),
                    'default_id' => $settings->get('core.default_ticket_cat') ?: null,
                ],
                'workflow' => [
                    'enabled'    => $field_manager->isWorkflowEnabled(),
                    'default_id' => $settings->get('core.default_ticket_work') ?: null,
                ],
                'priority' => [
                    'enabled'    => $field_manager->isPriorityEnabled(),
                    'default_id' => $settings->get('core.default_ticket_pri') ?: null,
                ],
                'custom' => [
                    'has_any' => count($field_manager->getFields()) > 0,
                ],
            ],
            'billing' => [
                'enabled'       => $settings->get('core_tickets.enable_billing'),
                'currency_name' => $settings->get('core_tickets.enable_billing') ? $settings->get('core_tickets.billing_currency') : null,
            ],
            'timelog' => [
                'enabled' => $settings->get('core_tickets.enable_timelog'),
            ],
            'group_fields' => $group_fields,
            'order_fields' => $order_fields,
        ];

        $data['chat'] = [
            'enabled' => $settings->get('core.apps_chat') && $me->hasPerm('agent_chat.use'),
        ];

        $data['crm'] = [
            'enabled' => $me->hasPerm('agent_people.use'),
        ];

        $data['feedback'] = [
            'enabled' => $me->hasPerm('core.apps_feedback'),
        ];

        $data['publish'] = [
            'enabled' => $me->hasPerm('core.apps_kb'),
        ];

        $data['tasks'] = [
            'enabled' => $me->hasPerm('core.apps_tasks'),
        ];

        return View::create(
            $this->dataSerialize(new PrimitiveArray($data)),
            Response::HTTP_OK
        );
    }
}
