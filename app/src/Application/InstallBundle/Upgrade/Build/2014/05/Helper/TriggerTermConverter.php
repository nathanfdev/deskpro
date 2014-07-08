<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

use Application\DeskPRO\Tickets\Triggers\Terms;
use DeskPRO\Kernel\KernelErrorHandler;
use Orb\Util\Arrays;
use Orb\Util\OptionsArray;
use Orb\Util\Strings;

class TriggerTermConverter
{
	/**
	 * @var array
	 */
	private $mappings;

	public function __construct(array $mappings = array())
	{
		$this->mappings = $mappings;
	}

	public function getTriggerTerm($event_trigger, $info)
	{
		$t = $info['type'];
		$t = preg_replace('#\[\d+\]$#', '', $t); // something[123] to just something

		$func = "upgradeTerm_{$t}";

		if (!method_exists($this, $func)) {
			$e = new \Exception("Unknown trigger term: " . $info['type']);
			KernelErrorHandler::logException($e);
			return null;
		}

		return $this->$func($info['type'], $info['op'], new OptionsArray($info['options']), $event_trigger);
	}


	/**
	 * @param OptionsArray $options
	 * @param string $key
	 * @return array
	 */
	private function getSingleArrayValue(OptionsArray $options, $key)
	{
		// array('myoption' => xyz)
		if (isset($options[$key])) {
			$val = $options[$key];

		// array(array('xyz')) or array('xyz')
		} else if (isset($options[0])) {
			$val = $options[0];

		// unknown
		} else {
			$val = array();
		}

		if (!is_array($val)) {
			$val = array($val);
		}

		return $val;
	}

	private function upgradeTerm_agent($type, $op, OptionsArray $options)
	{
		$opt = $this->getSingleArrayValue($options, 'agent');
		return new Terms\CheckAgent($op, array('agent_ids' => $opt));
	}

	private function upgradeTerm_agent_team($type, $op, OptionsArray $options)
	{
		$opt = $this->getSingleArrayValue($options, 'agent_team');
		return new Terms\CheckAgentTeam($op, array('team_ids' => $opt));
	}

	private function upgradeTerm_api_key($type, $op, OptionsArray $options)
	{
		return new Terms\CheckApiKey($op, array('api_key_id' => $options->get('api_key', 0)));
	}

	private function upgradeTerm_category($type, $op, OptionsArray $options)
	{
		$opt = $this->getSingleArrayValue($options, 'category');
		return new Terms\CheckCategory($op, array('category_ids' => $opt));
	}

	private function upgradeTerm_creation_system($type, $op, OptionsArray $options)
	{
		return new Terms\CheckCreationSystem($op, array('creation_system' => $options->get('creation_system')));
	}

	private function upgradeTerm_creation_system_option($type, $op, OptionsArray $options)
	{
		return new Terms\CheckCreationSystemOption($op, array('creation_system_option' => $options->get('website_url')));
	}

	private function upgradeTerm_current_day($type, $op, OptionsArray $options)
	{
		// before: date('w') (0=sun), new: iso8601 (1=mon, 7=sun)
		$opt = array_map(function($d) {
			$d = (int)$d;
			if ($d == 0) return 7;
			else return $d;
		}, $this->getSingleArrayValue($options, 'days'));

		return new Terms\CheckDayOfWeek($op, array('days' => $opt, 'tz' => 'UTC', 'var' => 'now'));
	}

	private function upgradeTerm_day_created($type, $op, OptionsArray $options)
	{
		// before: date('w') (0=sun), new: iso8601 (1=mon, 7=sun)
		$opt = array_map(function($d) {
			$d = (int)$d;
			if ($d == 0) return 7;
			else return $d;
		}, $this->getSingleArrayValue($options, 'days'));

		return new Terms\CheckDayOfWeek($op, array('days' => $opt, 'tz' => 'UTC', 'var' => 'date_created'));
	}

	private function upgradeTerm_department($type, $op, OptionsArray $options)
	{
		$opt = $this->getSingleArrayValue($options, 'department');
		return new Terms\CheckDepartment($op, array('department_ids' => $opt));
	}

	private function upgradeTerm_email_account_bcc($type, $op, OptionsArray $options)
	{
		// no translation
		return null;
	}

	private function upgradeTerm_email_body($type, $op, OptionsArray $options)
	{
		return new Terms\CheckEmailBody($op, array('body' => $options->get('message', 'NO MESSAGE')));
	}

	private function upgradeTerm_email_cc_email($type, $op, OptionsArray $options)
	{
		return new Terms\CheckEmailCcAddress($op, array('email' => $options->get('email', 'NO EMAIL')));
	}

	private function upgradeTerm_email_from_email($type, $op, OptionsArray $options)
	{
		return new Terms\CheckEmailFromAddress($op, array('email' => $options->get('email', 'NO EMAIL')));
	}

	private function upgradeTerm_email_subject($type, $op, OptionsArray $options)
	{
		return new Terms\CheckEmailSubject($op, array('subject' => $options->get('subject', 'NO SUBJECT')));
	}

	private function upgradeTerm_email_to_email($type, $op, OptionsArray $options)
	{
		return new Terms\CheckEmailToAddress($op, array('email' => $options->get('email', 'NO EMAIL')));
	}

	private function upgradeTerm_email_header($type, $op, OptionsArray $options)
	{
		return new Terms\CheckEmailHeader($op, array(
			'name' => $options->get('header_name', 'NO NAME'),
			'value' => $options->get('header_value', 'NO VALUE'),
		));
	}

	private function upgradeTerm_feedback_rating($type, $op, OptionsArray $options)
	{
		switch ($options->get('rating')) {
			case 'positive': $r = 1;  break;
			case 'negative': $r = -1; break;
			default:         $r = 0; break;
		}
		return new Terms\CheckSatisfaction($op, array('rating' => $r));
	}

	private function upgradeTerm_gateway_account($type, $op, OptionsArray $options)
	{
		// new email accounts use old gateway account ids, so can use same option
		return new Terms\CheckEmailAccount($op, array('email_account_ids' => array($options->get('gateway_account', 0))));
	}

	private function upgradeTerm_is_via_email($type, $op, OptionsArray $options)
	{
		// Not a criteria anymore
		// instead: mode=email
		return null;
	}

	private function upgradeTerm_is_via_email_reply($type, $op, OptionsArray $options)
	{
		// Not a criteria anymore
		// instead: event_trigger=reply, mode=email
		return null;
	}

	private function upgradeTerm_is_via_interface($type, $op, OptionsArray $options)
	{
		// Not a criteria anymore
		// instead: mode=web,form,portal,widget
		return null;
	}

	private function upgradeTerm_label($type, $op, OptionsArray $options)
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

		if ($op == 'is') $op = 'contains';
		else if ($op == 'not') $op = 'notcontains';

		return new Terms\CheckLabel($op, array('labels' => $labels));
	}

	private function upgradeTerm_message($type, $op, OptionsArray $options, $event_trigger)
	{
		$message = $options->get('message', 'NO MESSAGE');

		if (strpos($event_trigger, 'agent') !== false) {
			return new Terms\CheckAgentMessage($op, array('message' => $message));
		} else {
			return new Terms\CheckUserMessage($op, array('message' => $message));
		}
	}

	private function upgradeTerm_new_reply_agent($type, $op, OptionsArray $options)
	{
		return new Terms\CheckAgentMessage('isset');
	}

	private function upgradeTerm_new_reply_note($type, $op, OptionsArray $options)
	{
		return new Terms\CheckAgentNote('isset');
	}

	private function upgradeTerm_new_reply_user($type, $op, OptionsArray $options)
	{
		return new Terms\CheckUserMessage('isset');
	}

	private function upgradeTerm_org_email_domain($type, $op, OptionsArray $options)
	{
		return new Terms\CheckOrgEmailDomain($op, array('email_domain' => $options->get('email_domain', 'NO DOMAIN')));
	}

	private function upgradeTerm_org_label($type, $op, OptionsArray $options)
	{
		$labels = explode(',', $options->get('label', ''));
		$labels = Arrays::func($labels, 'trim');
		return new Terms\CheckOrgLabel($op, array('labels' => $labels));
	}

	private function upgradeTerm_org_manager($type, $op, OptionsArray $options)
	{
		return new Terms\CheckUserOrgManager($op);
	}

	private function upgradeTerm_org_name($type, $op, OptionsArray $options)
	{
		return new Terms\CheckOrgName($op, array('name' => $options->get('name', 'NO NAME')));
	}

	private function upgradeTerm_person_email($type, $op, OptionsArray $options)
	{
		return new Terms\CheckUserEmail($op, array('email' => $options->get('email', 'NO EMAIL')));
	}

	private function upgradeTerm_person_email_domain($type, $op, OptionsArray $options)
	{
		$domain = $options->get('email_domain', 'NO DOMAIN');
		$regex = '/^(.*?)@' . preg_quote($domain, '/') . '$/';

		switch ($op) {
			case 'is': $op = 'is_regex'; break;
			default:   $op = 'not_regex'; break;
		}

		return new Terms\CheckUserEmail($op, array('email' => $regex));
	}

	private function upgradeTerm_person_field($type, $op, OptionsArray $options)
	{
		$field_id = Strings::extractRegexMatch('#\[(\d+)\]$#', $type);
		if (!$field_id) return null;

		$value = Arrays::getValue($options->all(), 'custom_fields.field_'. $field_id);

		return new Terms\CheckUserField($op, array(
			'field_id' => $field_id,
			'value'    => $value
		));
	}

	private function upgradeTerm_agent_performer($type, $op, OptionsArray $options)
	{
		return new Terms\CheckPerformer($op, array(
			'person_ids' => $this->getSingleArrayValue($options, 'agent_ids')
		));
	}

	private function upgradeTerm_person_name($type, $op, OptionsArray $options)
	{
		return new Terms\CheckUserName($op, array(
			'name' => $options->get('name', 'NO NAME')
		));
	}

	private function upgradeTerm_person_label($type, $op, OptionsArray $options)
	{
		$labels = explode(',', $options->get('label', ''));
		$labels = Arrays::func($labels, 'trim');
		return new Terms\CheckUserLabel($op, array('labels' => $labels));
	}

	private function upgradeTerm_person_usergroup($type, $op, OptionsArray $options)
	{
		$opt = $this->getSingleArrayValue($options, 'usergroup');
		return new Terms\CheckUserUsergroups($op, array('usergroup_ids' => $opt));
	}

	private function upgradeTerm_priority($type, $op, OptionsArray $options)
	{
		$opt = $this->getSingleArrayValue($options, 'priority');
		return new Terms\CheckPriority($op, array('priority_ids' => $opt));
	}

	private function upgradeTerm_product($type, $op, OptionsArray $options)
	{
		$opt = $this->getSingleArrayValue($options, 'product');
		if (!$opt) {
			// badly named in old version
			$opt = $this->getSingleArrayValue($options, 'category');
		}

		return new Terms\CheckProduct($op, array('product_ids' => $opt));
	}

	private function upgradeTerm_sla($type, $op, OptionsArray $options)
	{
		return new Terms\CheckSla($op, array('sla_ids' => $this->getSingleArrayValue($options, 'sla_id')));
	}

	private function upgradeTerm_sla_status($type, $op, OptionsArray $options)
	{
		$status = $options->get('sla_status', 'warning');
		if ($status == 'warn') {
			$status = 'warning'; //the real status id
		}

		return new Terms\CheckSlaStatus($op, array(
			'sla_ids'    => $this->getSingleArrayValue($options, 'sla_id'),
			'sla_status' => $status
		));
	}

	private function upgradeTerm_status($type, $op, OptionsArray $options)
	{
		return new Terms\CheckStatus($op, array('status' => $options->get('status', null)));
	}

	private function upgradeTerm_subject($type, $op, OptionsArray $options)
	{
		return new Terms\CheckSubject($op, array('subject' => $options->get('subject', 'NO SUBJECT')));
	}

	private function upgradeTerm_ticket_field($type, $op, OptionsArray $options)
	{
		$field_id = Strings::extractRegexMatch('#\[(\d+)\]$#', $type);
		if (!$field_id) return null;

		$value = Arrays::getValue($options->all(), 'custom_fields.field_'. $field_id);

		return new Terms\CheckTicketField($op, array(
			'field_id' => $field_id,
			'value'    => $value
		));
	}

	private function upgradeTerm_time_created($type, $op, OptionsArray $options)
	{
		return new Terms\CheckTimeOfDay($op, array(
			'time1' => $options->get('hour1', '00') . ':' . $options->get('minute1', '00'),
			'tz'    => 'UTC',
			'var'   => 'date_created'
		));
	}

	private function upgradeTerm_date_created($type, $op, OptionsArray $options)
	{
		return new Terms\CheckDateCreated($op, array(
			'date1'               => $options->get('date1'),
			'date2'               => $options->get('date2'),
			'date1_relative'      => $options->get('date1_relative'),
			'date2_relative'      => $options->get('date2_relative'),
			'date1_relative_type' => $options->get('date1_relative_type'),
			'date2_relative_type' => $options->get('date2_relative_type'),
		));
	}

	private function upgradeTerm_urgency($type, $op, OptionsArray $options)
	{
		return new Terms\CheckUrgency($op, array('urgency1' => $options->get('num', 1)));
	}

	private function upgradeTerm_user_performer_email($type, $op, OptionsArray $options)
	{
		return new Terms\CheckPerformerEmail($op, array('email' => $options->get('user_email')));
	}

	private function upgradeTerm_workflow($type, $op, OptionsArray $options)
	{
		$opt = $this->getSingleArrayValue($options, 'workflow');
		return new Terms\CheckWorkflow($op, array('workflow_ids' => $opt));
	}
}