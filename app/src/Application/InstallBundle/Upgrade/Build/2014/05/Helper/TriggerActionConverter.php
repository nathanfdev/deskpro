<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\InstallBundle\Upgrade\Build\Helper201405;

use Application\DeskPRO\Tickets\Actions;
use DeskPRO\Kernel\KernelErrorHandler;
use Orb\Util\Arrays;
use Orb\Util\OptionsArray;
use Orb\Util\Strings;

class TriggerActionConverter
{
    /**
     * @var array
     */
    private $mappings;

    public function __construct(array $mappings = array())
    {
        $this->mappings = $mappings;
    }

    public function getTriggerAction($info)
    {
        $t = $info['type'];
        $t = preg_replace('#\[\d+\]$#', '', $t); // something[123] to just something

        $func = "upgradeAction_{$t}";

        if (!method_exists($this, $func)) {
            $e = new \Exception("Unknown trigger action: " . $info['type']);
            KernelErrorHandler::logException($e);

            return null;
        }

        try {
            return $this->$func($info['type'], new OptionsArray($info['options']));
        } catch (\Exception $e) {
            $e = new \Exception("Invalid trigger option: " . $e->getMessage());
            KernelErrorHandler::logException($e);

            return null;
        }
    }

    private function upgradeAction_add_agent_notify($type, OptionsArray $options)
    {
        // no translation
        return null;
    }

    private function upgradeAction_add_cc($type, OptionsArray $options)
    {
        $emails = $options->get('add_emails', '');
        $emails = explode(',', $emails);
        $emails = Arrays::func($emails, 'trim');

        return new Actions\SetCcs(array('add_emails' => $emails));
    }

    private function upgradeAction_add_labels($type, OptionsArray $options)
    {
        if ($options->get('label')) {
            $labels = explode(',', $options->get('label', ''));
        } else {
            $labels = $options->get('labels', array());
            if (!is_array($labels)) {
                $labels = array($labels);
            }
        }

        $labels = Arrays::func($labels, 'trim');
        $labels = Arrays::removeEmptyString($labels);

        if (!$labels) {
            return null;
        }

        return new Actions\SetLabels(array('add_labels' => $labels));
    }

    private function upgradeAction_add_org_managers($type, OptionsArray $options)
    {
        return new Actions\SetCcs(array('add_org_managers' => true));
    }

    private function upgradeAction_add_participants($type, OptionsArray $options)
    {
        return new Actions\SetAgentFollowers(array('add_agent_ids' => $options->get('add_participants', array())));
    }

    private function upgradeAction_add_sla($type, OptionsArray $options)
    {
        return new Actions\SetSlas(array('add_sla_ids' => array($options->get('sla_id', 0))));
    }

    private function upgradeAction_agent($type, OptionsArray $options)
    {
        return new Actions\SetAgent(array('agent_id' => $options->get('agent')));
    }

    private function upgradeAction_agent_team($type, OptionsArray $options)
    {
        return new Actions\SetAgentTeam(array('agent_team_id' => $options->get('agent_team')));
    }

    private function upgradeAction_category($type, OptionsArray $options)
    {
        return new Actions\SetCategory(array('category_id' => $options->get('category')));
    }

    private function upgradeAction_delete($type, OptionsArray $options)
    {
        return new Actions\SetDeleted();
    }

    private function upgradeAction_department($type, OptionsArray $options)
    {
        return new Actions\SetDepartment(array('department_id' => $options->get('department')));
    }

    private function upgradeAction_disable_agent_notifications($type, OptionsArray $options)
    {
        return new Actions\ModMuteAgentEmails();
    }

    private function upgradeAction_disable_notifications($type, OptionsArray $options)
    {
        return array(
            new Actions\ModMuteAgentEmails(),
            new Actions\ModMuteUserEmails()
        );
    }

    private function upgradeAction_disable_user_notifications($type, OptionsArray $options)
    {
        return new Actions\ModMuteUserEmails();
    }

    private function upgradeAction_enable_new_ticket_confirmation($type, OptionsArray $options)
    {
        return new Actions\SendUserEmail(array(
            'template' => 'DeskPRO:emails_user:ticket-new-autoreply.html.twig',
            'do_cc_users' => true,
            'from_name' => 'helpdesk_name',
        ));
    }

    private function upgradeAction_enable_user_notification_new_reply_user($type, OptionsArray $options)
    {
        return new Actions\SendUserEmail(array(
            'template' => 'DeskPRO:emails_user:ticket-reply-autoreply.html.twig',
            'do_cc_users' => true,
            'from_name' => 'helpdesk_name',
        ));
    }

    private function upgradeAction_flag($type, OptionsArray $options)
    {
        return new Actions\SetFlag(array('color' => $options->get('flag', 'red')));
    }

    private function upgradeAction_force_email_validation($type, OptionsArray $options)
    {
        return new Actions\ModSetEmailValidation(array('enable_validation' => true));
    }

    private function upgradeAction_hipchat_message($type, OptionsArray $options)
    {
        return new \deskpro_hipchat\Ticket\Actions\HipChatAction(array('room' => $options->get('room', 0)));
    }

    private function upgradeAction_hold($type, OptionsArray $options)
    {
        return new Actions\SetHold(array('is_hold' => (bool)$options->get('is_hold')));
    }

    private function upgradeAction_language($type, OptionsArray $options)
    {
        return new Actions\SetLanguage(array('language_id' => $options->get('language', 0)));
    }

    private function upgradeAction_priority($type, OptionsArray $options)
    {
        return new Actions\SetPriority(array('priority_id' => $options->get('priority')));
    }

    private function upgradeAction_product($type, OptionsArray $options)
    {
        return new Actions\SetProduct(array('product_id' => $options->get('product')));
    }

    private function upgradeAction_recalculate_sla_status($type, OptionsArray $options)
    {
        // no translation (not required)
        return null;
    }

    private function upgradeAction_remove_labels($type, OptionsArray $options)
    {
        if ($options->get('label')) {
            $labels = explode(',', $options->get('label', ''));
        } else {
            $labels = $options->get('labels', array());
            if (!is_array($labels)) {
                $labels = array($labels);
            }
        }

        $labels = Arrays::func($labels, 'trim');
        $labels = Arrays::removeEmptyString($labels);

        if (!$labels) {
            return null;
        }

        return new Actions\SetLabels(array('remove_labels' => $labels));
    }

    private function upgradeAction_remove_sla($type, OptionsArray $options)
    {
        return new Actions\SetSlas(array('remove_sla_ids' => array($options->get('sla_id', 0))));
    }

    private function upgradeAction_reply($type, OptionsArray $options)
    {
        return new Actions\AddAgentReply(array(
            'reply_text'        => $options->get('reply_text'),
            'by_assigned_agent' => true,
            'by_agent_id'       => 1,
            'no_formatter'      => false
        ));
    }

    private function upgradeAction_send_agent_email($type, OptionsArray $options)
    {
        return new Actions\SendAgentEmail(array(
            'agent_ids' => $options->get('agents', array()),
            'template' => $this->getNewTemplateName($options->get('template_name'))
        ));
    }

    private function upgradeAction_send_autoclose_warn_email($type, OptionsArray $options)
    {
        return new Actions\SendUserEmail(array(
            'template' => $this->getNewTemplateName('DeskPRO:emails_user:ticket-autoclose-warn.html.twig'),
        ));
    }

    private function upgradeAction_send_feedback_email($type, OptionsArray $options)
    {
        return new Actions\SendUserEmail(array(
            'template' => 'DeskPRO:emails_user:ticket-rate.html.twig',
        ));
    }

    private function upgradeAction_send_org_managers_email($type, OptionsArray $options)
    {
        // no translation (requires full email template)
        return null;
    }

    private function upgradeAction_send_user_email($type, OptionsArray $options)
    {
        return new Actions\SendUserEmail(array(
            'template' => $this->getNewTemplateName($options->get('template_name'))
        ));
    }

    private function upgradeAction_set_agent_email_template_newticket($type, OptionsArray $options)
    {
        // no translation
        return null;
    }

    private function upgradeAction_set_email_template_user_new_ticket($type, OptionsArray $options)
    {
        // no translation
        return null;
    }

    private function upgradeAction_set_from_address($type, OptionsArray $options)
    {
        // no translation
        return null;
    }

    private function upgradeAction_set_from_address_agent($type, OptionsArray $options)
    {
        // no translation
        return null;
    }

    private function upgradeAction_set_from_name($type, OptionsArray $options)
    {
        // no translation
        return null;
    }

    private function upgradeAction_set_from_name_agent($type, OptionsArray $options)
    {
        // no translation
        return null;
    }

    private function upgradeAction_set_gateway_address($type, OptionsArray $options)
    {
        $address_id = $options->get('gateway_address_id', 0);
        if (!isset($this->mappings['gateway_address_to_email_account'][$address_id])) {
            return null;
        }

        $id = $this->mappings['gateway_address_to_email_account'][$address_id];

        return new Actions\SetEmailAccount(array('email_account_id'=> $id));
    }

    private function upgradeAction_set_initial_from_name($type, OptionsArray $options)
    {
        // no translation
        return null;
    }

    private function upgradeAction_set_sla_complete($type, OptionsArray $options)
    {
        if ($options->get('sla_complete')) {
            return new Actions\SetSlasComplete(array(
                'sla_ids' => array($options->get('sla_id')),
                'sla_status' => 'nochange'
            ));
        } else {
            return new Actions\SetSlaReset(array('sla_ids' => array($options->get('sla_id'))));
        }
    }

    private function upgradeAction_set_sla_status($type, OptionsArray $options)
    {
        return new Actions\SetSlasComplete(array(
            'sla_ids' => array($options->get('sla_id')),
            'sla_status' => $options->get('sla_status')
        ));
    }

    private function upgradeAction_set_user_email_template_newreply_agent($type, OptionsArray $options)
    {
        // no translation
        return null;
    }

    private function upgradeAction_set_user_email_template_newticket($type, OptionsArray $options)
    {
        // no translation
        return null;
    }

    private function upgradeAction_status($type, OptionsArray $options)
    {
        return new Actions\SetStatus(array('status' => $options->get('status')));
    }

    private function upgradeAction_stop_actions($type, OptionsArray $options)
    {
        return new Actions\ModStopTriggers();
    }

    private function upgradeAction_subject($type, OptionsArray $options)
    {
        return new Actions\SetSubject(array('subject' => $options->get('subject')));
    }

    private function upgradeAction_ticket_field($type, OptionsArray $options)
    {
        $field_id = Strings::extractRegexMatch('#\[(\d+)\]$#', $type);
        if (!$field_id) return null;

        $value = Arrays::getValue($options->all(), 'custom_fields.field_'. $field_id);

        return new Actions\SetTicketField(array(
            'field_id' => $field_id,
            'value'    => $value
        ));
    }

    private function upgradeAction_urgency($type, OptionsArray $options)
    {
        $num = $options->get('num');
        if ($num > 0) {
            $mode = Actions\SetUrgency::MODE_ADD;
        } else {
            $num = abs($num);
            $mode = Actions\SetUrgency::MODE_SUB;
        }

        return new Actions\SetUrgency(array(
            'urgency' => $num,
            'mode'    => $mode
        ));
    }

    private function upgradeAction_urgency_set($type, OptionsArray $options)
    {
        return new Actions\SetUrgency(array(
            'urgency' => $options->get('num'),
            'mode'    => $options->get('allow_lower') ? Actions\SetUrgency::MODE_RAISE : Actions\SetUrgency::MODE_SET
        ));
    }

    private function upgradeAction_workflow($type, OptionsArray $options)
    {
        return new Actions\SetWorkflow(array('workflow_id' => $options->get('workflow')));
    }

    private function getNewTemplateName($name)
    {
        static $map = array(
            'DeskPRO:emails_user:new-reply-agent.html.twig' => 'DeskPRO:emails_user:ticket-reply-byagent.html.twig',
            'DeskPRO:emails_user:new-reply-user.html.twig'  => 'DeskPRO:emails_user:ticket-reply-autoreply.html.twig',
            'DeskPRO:emails_user:new-ticket.html.twig'      => 'DeskPRO:emails_user:ticket-new-autoreply.html.twig',
        );

        if (isset($map[$name])) {
            return $map[$name];
        }

        $new_name = preg_replace('#^DeskPRO:emails_(user|agent):custom_(.*?)\.html\.twig$#', 'DeskPRO:emails_custom:$1_$2.html.twig', $name);

        return $new_name;
    }
}
