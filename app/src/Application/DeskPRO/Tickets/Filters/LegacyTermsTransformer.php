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
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Filters;

use Application\DeskPRO\Criteria\CriteriaTermInterface;
use Application\DeskPRO\Tickets\Filters\Terms\FilterTermComposite;
use Application\DeskPRO\Tickets\Filters\Terms\FilterTermInterface;
use Application\DeskPRO\Tickets\Filters\Terms;
use Orb\Util\Util;

/**
 * This converts between 'new' and 'old' style term definitions.
 *
 * New style terms are built using FilterTerms and one object per term. This allows for better type-checking
 * and better organisation of term logic.
 *
 * Old style is just a plain array of terms. We started to replace it (like we did with Triggers with TriggerActions),
 * but ran out of time because it affects so many things: ticket searching, escalations, filters.
 *
 * The admin interface is built up around the idea of the new system though, it's how it accepts and processes
 * forms for filters and escalations. So we need this transformer to convert new-style into old-style and visaversa.
 */
class LegacyTermsTransformer
{
	/**
	 * Convert FilterTerms into legacy-style terms array.
	 *
	 * @param FilterTerms $terms
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	public function toLegacyTerms(FilterTerms $terms)
	{
		$legacy_terms = array();

		$all_terms = $terms->getTerms();

		foreach ($all_terms as $term) {
			$t = $this->_termToLegacyTerms($term);

			if (!$t) {
				throw new \InvalidArgumentException("New term has no mapping to legacy term: " . get_class($terms));
			}

			if (isset($t['type'])) {
				$t = array($t);
			}

			$legacy_terms = array_merge($legacy_terms, $t);
		}

		return $legacy_terms;
	}


	/**
	 * @param CriteriaTermInterface $term
	 * @return array Returns an array of replacement terms (usually only one, but possibly multiple if there is a non-exact match)
	 */
	private function _termToLegacyTerms($term)
	{
		$legacy_terms = array();

		if ($term instanceof FilterTermComposite) {
			foreach ($term->getAll() as $subterm) {
				$t = $this->_termToLegacyTerms($subterm);
				if ($t) {
					$legacy_terms = array_merge($legacy_terms, $t);
				}
			}
			return $legacy_terms;
		}

		$options = $term->getTermOptions();

		switch (Util::getBaseClassname($term)) {
			case 'FilterAgent':
				return array(
					'type'    => 'agent',
					'op'      => $term->getTermOperator(),
					'options' => array('agent' => $options['agent_ids'])
				);

			case 'FilterAgentParticipant':
				return array(
					'type'    => 'participant',
					'op'      => $term->getTermOperator(),
					'options' => array('agent' => $options['agent_ids'])
				);

			case 'FilterAgentTeam':
				return array(
					'type'    => 'agent_team',
					'op'      => $term->getTermOperator(),
					'options' => array('agent_team' => $options['team_ids'])
				);

			case 'FilterCategory':
				return array(
					'type'    => 'category',
					'op'      => $term->getTermOperator(),
					'options' => array('category' => $options['category_ids'])
				);

			case 'FilterDepartment':
				return array(
					'type'    => 'department',
					'op'      => $term->getTermOperator(),
					'options' => array('department' => $options['department_ids'])
				);

			case 'FilterHoldStatus':
				return array(
					'type'    => 'is_hold',
					'op'      => $term->getTermOperator(),
					'options' => array('is_hold' => $options['is_hold'])
				);

			case 'FilterLabels':
				return array(
					'type'    => 'label',
					'op'      => $term->getTermOperator(),
					'options' => array('labels' => $options['labels'])
				);

			case 'FilterLanguage':
				return array(
					'type'    => 'language',
					'op'      => $term->getTermOperator(),
					'options' => array('language' => $options['language_ids'])
				);

			case 'FilterOrgEmailDomain':
				return array(
					'type'    => 'org_email_domain',
					'op'      => $term->getTermOperator(),
					'options' => array('email_domain' => $options['domain'])
				);

			case 'FilterOrgId':
				return array(
					'type'    => 'organization',
					'op'      => $term->getTermOperator(),
					'options' => array('organization' => $options['org_ids'])
				);

			case 'FilterOrgLabels':
				return array(
					'type'    => 'org_label',
					'op'      => $term->getTermOperator(),
					'options' => array('labels' => $options['labels'])
				);

			case 'FilterPriority':
				return array(
					'type'    => 'priority',
					'op'      => $term->getTermOperator(),
					'options' => array('priority' => $options['priority_ids'])
				);

			case 'FilterProduct':
				return array(
					'type'    => 'product',
					'op'      => $term->getTermOperator(),
					'options' => array('product' => $options['product_ids'])
				);

			case 'FilterStatus':
				return array(
					'type'    => 'status',
					'op'      => $term->getTermOperator(),
					'options' => array('status' => $options['status'])
				);

			case 'FilterSubject':
				return array(
					'type'    => 'subject',
					'op'      => $term->getTermOperator(),
					'options' => array('subject' => $options['subject'])
				);

			case 'FilterUrgency':
				return array(
					'type'    => 'urgency',
					'op'      => $term->getTermOperator(),
					'options' => array('num' => $options['urgency'])
				);

			case 'FilterUserEmailAddress':
				if ($options['email'][0] == '@') {
					return array(
						'type'    => 'person_email_domain',
						'op'      => $term->getTermOperator(),
						'options' => array('email_domain' => substr($options['email'], 1))
					);
				} else {
					return array(
						'type'    => 'person_email',
						'op'      => $term->getTermOperator(),
						'options' => array('email' => $options['email'])
					);
				}

			case 'FilterUserGroups':
				return array(
					'type'    => 'person_usergroup',
					'op'      => $term->getTermOperator(),
					'options' => array('usergroup' => $options['group_ids'])
				);

			case 'FilterUserLabels':
				return array(
					'type'    => 'person_label',
					'op'      => $term->getTermOperator(),
					'options' => array('labels' => $options['labels'])
				);

			case 'FilterWorkflow':
				return array(
					'type'    => 'workflow',
					'op'      => $term->getTermOperator(),
					'options' => array('workflow' => $options['workflow_ids'])
				);

			case 'FilterEmailAccount':
				return array(
					'type'    => 'email_account',
					'op'      => $term->getTermOperator(),
					'options' => array('email_account_ids' => $options['email_account_ids'])
				);

			case 'FilterSlaStatus':
				return array(
					'type'    => 'sla_status',
					'op'      => $term->getTermOperator(),
					'options' => $options->all()
				);

			case 'FilterSla':
				return array(
					'type'    => 'sla',
					'op'      => $term->getTermOperator(),
					'options' => $options->all()
				);

			case 'FilterUserContactPhone':
				return array(
					'type'    => 'person_contact_phone',
					'op'      => $term->getTermOperator(),
					'options' => $options->all()
				);

			case 'FilterUserContactAddress':
				return array(
					'type'    => 'person_contact_address',
					'op'      => $term->getTermOperator(),
					'options' => $options->all()
				);

			case 'FilterUserContactIm':
				return array(
					'type'    => 'person_contact_im',
					'op'      => $term->getTermOperator(),
					'options' => $options->all()
				);

			case 'FilterOrgContactPhone':
				return array(
					'type'    => 'org_contact_phone',
					'op'      => $term->getTermOperator(),
					'options' => $options->all()
				);

			case 'FilterOrgContactAddress':
				return array(
					'type'    => 'org_contact_address',
					'op'      => $term->getTermOperator(),
					'options' => $options->all()
				);

			case 'FilterOrgContactIm':
				return array(
					'type'    => 'org_contact_im',
					'op'      => $term->getTermOperator(),
					'options' => $options->all()
				);

			case 'FilterDateCreated':
				return array(
					'type'    => 'date_created',
					'op'      => $term->getTermOperator(),
					'options' => $options->all()
				);

			case 'FilterDateResolved':
				return array(
					'type'    => 'date_resolved',
					'op'      => $term->getTermOperator(),
					'options' => $options->all()
				);

			case 'FilterDateClosed':
				return array(
					'type'    => 'date_closed',
					'op'      => $term->getTermOperator(),
					'options' => $options->all()
				);

			case 'FilterDateLastAgentReply':
				return array(
					'type'    => 'date_last_agent_reply',
					'op'      => $term->getTermOperator(),
					'options' => $options->all()
				);

			case 'FilterDateLastUserReply':
				return array(
					'type'    => 'date_last_user_reply',
					'op'      => $term->getTermOperator(),
					'options' => $options->all()
				);

			case 'FilterUserDateCreated':
				return array(
					'type'    => 'person_date_created',
					'op'      => $term->getTermOperator(),
					'options' => $options->all()
				);

			case 'FilterOrgName':
				return array(
					'type'    => 'org_name',
					'op'      => $term->getTermOperator(),
					'options' => $options->all()
				);

			case 'FilterOrgDateCreated':
				return array(
					'type'    => 'org_date_created',
					'op'      => $term->getTermOperator(),
					'options' => $options->all()
				);

			case 'FilterUserWaiting':
				$t = $term->getTermOptions();
				$t = $t['time'];
				return array(
					'type'    => 'user_waiting',
					'op'      => $term->getTermOperator(),
					'options' => array(
						'waiting_time'      => $t[0],
						'waiting_time_unit' => $t[1]
					)
				);

			case 'FilterTotalUserWaiting':
				$t = $term->getTermOptions();
				$t = $t['time'];
				return array(
					'type'    => 'total_user_waiting',
					'op'      => $term->getTermOperator(),
					'options' => array(
						'waiting_time'      => $t[0],
						'waiting_time_unit' => $t[1]
					)
				);

			case 'FilterTicketField':
				$t = $term->getTermOptions();
				$fid = $t['field_id'];
				return array(
					'type'    => "ticket_field[{$fid}]",
					'op'      => $term->getTermOperator(),
					'options' => array(
						'custom_fields' => array(
							"field_{$fid}" => @$t['value'] ?: null
						)
					)
				);

			case 'FilterUserField':
				$t = $term->getTermOptions();
				$fid = $t['field_id'];
				return array(
					'type'    => "person_field[{$fid}]",
					'op'      => $term->getTermOperator(),
					'options' => array(
						'custom_fields' => array(
							"field_{$fid}" => @$t['value'] ?: null
						)
					)
				);

			case 'FilterOrgField':
				$t = $term->getTermOptions();
				$fid = $t['field_id'];
				return array(
					'type'    => "org_field[{$fid}]",
					'op'      => $term->getTermOperator(),
					'options' => array(
						'custom_fields' => array(
							"field_{$fid}" => @$t['value'] ?: null
						)
					)
				);
		}

		return $legacy_terms;
	}


	/**
	 * Convert legacy-style terms array into FilterTerms.
	 *
	 * @param array $legacy_terms
	 * @return FilterTerms Returns an array of replacement terms (usually only one, but possibly multiple if there is a non-exact match)
	 * @throws \InvalidArgumentException
	 */
	public function toFilterTerms(array $legacy_terms)
	{
		$terms = new FilterTerms();

		foreach ($legacy_terms as $term) {
			$t = $this->_legacyTermToFilterTerm($term);

			if (!$t) {
				throw new \InvalidArgumentException("Legacy term has no mapping to new term: " . @$term['type']);
			}

			$terms->addTerm($t);
		}

		return $terms;
	}


	/**
	 * @param array $legacy_term
	 * @return FilterTermInterface
	 */
	private function _legacyTermToFilterTerm(array $legacy_term)
	{
		if (empty($legacy_term['op'])) {
			return null;
		}

		$op = $legacy_term['op'];
		$options = $legacy_term['options'];

		$type_name = $legacy_term['type'];
		$type_id = null;
		if (preg_match('#^(.*?)\[(\d+)\]$#', $type_name, $m)) {
			$type_name = $m[1];
			$type_id   = $m[2];
		}

		switch ($type_name) {
			case 'subject':
				return new Terms\FilterSubject($op, array(
					'subject' => @$options['subject'] ?: ''
				));

			case 'department':
				$ids = @$options['department'] ?: array();
				if (!is_array($ids)) {
					$ids = array($ids);
				}

				return new Terms\FilterDepartment($op, array(
					'department_ids' => $ids
				));

			case 'agent':
				$ids = @$options['agent'] ?: array();
				if (!is_array($ids)) {
					$ids = array($ids);
				}

				return new Terms\FilterAgent($op, array(
					'agent_ids' => $ids
				));

			case 'agent_team':
				$ids = @$options['agent_team'] ?: array();
				if (!is_array($ids)) {
					$ids = array($ids);
				}

				return new Terms\FilterAgentTeam($op, array(
					'team_ids' => $ids
				));

			case 'participant':
				$ids = @$options['agent'] ?: array();
				if (!is_array($ids)) {
					$ids = array($ids);
				}

				return new Terms\FilterAgentParticipant($op, array(
					'agent_ids' => $ids
				));

			case 'label':
				$labels = @$options['labels'] ?: array();
				if (!is_array($labels)) {
					$labels = array($labels);
				}

				return new Terms\FilterLabels($op, array(
					'labels' => $labels
				));

			case 'status':
				$status = @$options['status'] ?: array();
				if (!is_array($status)) {
					$status = array($status);
				}

				return new Terms\FilterStatus($op, array(
					'status' => $status
				));

			case 'is_hold':
				return new Terms\FilterHoldStatus($op, array(
					'is_hold' => (bool)($options['is_hold'] ?: false)
				));

			case 'organization':
				$ids = @$options['organization'] ?: array();
				if (!is_array($ids)) {
					$ids = array($ids);
				}

				return new Terms\FilterOrgId($op, array(
					'org_ids' => $ids
				));

			case 'product':
				$ids = @$options['product'] ?: array();
				if (!is_array($ids)) {
					$ids = array($ids);
				}

				return new Terms\FilterProduct($op, array(
					'product_ids' => $ids
				));

			case 'category':
				$ids = @$options['category'] ?: array();
				if (!is_array($ids)) {
					$ids = array($ids);
				}

				return new Terms\FilterCategory($op, array(
					'category_ids' => $ids
				));

			case 'urgency':
				$urgency = @$options['num'] ?: 0;

				return new Terms\FilterUrgency($op, array(
					'urgency' => $urgency
				));

			case 'priority':
				$ids = @$options['priority'] ?: array();
				if (!is_array($ids)) {
					$ids = array($ids);
				}

				return new Terms\FilterPriority($op, array(
					'priority_ids' => $ids
				));

			case 'workflow':
				$ids = @$options['workflow'] ?: array();
				if (!is_array($ids)) {
					$ids = array($ids);
				}

				return new Terms\FilterWorkflow($op, array(
					'workflow_ids' => $ids
				));

			case 'email_account':
				$ids = @$options['email_account_ids'] ?: array();
				if (!is_array($ids)) {
					$ids = array($ids);
				}

				return new Terms\FilterEmailAccount($op, array(
					'email_account_ids' => $ids
				));

			case 'language':
				$ids = @$options['language'] ?: array();
				if (!is_array($ids)) {
					$ids = array($ids);
				}

				return new Terms\FilterLanguage($op, array(
					'language_ids' => $ids
				));

			case 'sla':
				return new Terms\FilterSla($op, array('sla_ids' => @$options['sla_ids'] ?: array()));

			case 'sla_status':
				return new Terms\FilterSlaStatus($op, array(
					'sla_ids'    => @$options['sla_ids'] ?: array(),
					'sla_status' => @$options['sla_status'] ?: ''
				));

			case 'user_waiting':
				return new Terms\FilterUserWaiting($op, array(
					'time' => array(
						@$options['waiting_time'] ?: 1,
						@$options['waiting_time_unit'] ?: 'days'
					)
				));

			case 'total_user_waiting':
				return new Terms\FilterTotalUserWaiting($op, array(
					'time' => array(
						@$options['waiting_time'] ?: 1,
						@$options['waiting_time_unit'] ?: 'days'
					)
				));

			case 'date_created':
				return new Terms\FilterDateCreated($op, $options);

			case 'date_resolved':
				return new Terms\FilterDateResolved($op, $options);

			case 'date_closed':
				return new Terms\FilterDateClosed($op, $options);

			case 'date_last_agent_reply':
				return new Terms\FilterDateLastAgentReply($op, $options);

			case 'date_last_user_reply':
				return new Terms\FilterDateLastUserReply($op, $options);

			case 'person_name':
				return new Terms\FilterUserName($op, array(
					'name' => @$options['name'] ?: ''
				));

			case 'person_email':
				return new Terms\FilterUserEmailAddress($op, array(
					'email' => @$options['email'] ?: ''
				));

			case 'person_email_domain':
				return new Terms\FilterUserEmailAddress($op, array(
					'email' => '@' . (@$options['email_domain'] ?: '')
				));

			case 'person_organization':
				$ids = @$options['organization'] ?: array();
				if (!is_array($ids)) {
					$ids = array($ids);
				}

				return new Terms\FilterOrgId($op, array(
					'org_ids' => $ids
				));

			case 'person_usergroup':
				$ids = @$options['usergroup'] ?: array();
				if (!is_array($ids)) {
					$ids = array($ids);
				}

				return new Terms\FilterUserGroups($op, array(
					'group_ids' => $ids
				));

			case 'person_username':
				return new Terms\FilterUserName($op, array(
					'name' => @$options['name'] ?: ''
				));

			case 'person_id':
				$ids = @$options['person'] ?: array();
				if (!is_array($ids)) {
					$ids = array($ids);
				}

				return new Terms\FilterUserId($op, array(
					'user_ids' => $ids
				));

			case 'person_language':
				$ids = @$options['language'] ?: array();
				if (!is_array($ids)) {
					$ids = array($ids);
				}

				return new Terms\FilterUserLanguage($op, array(
					'language_ids' => $ids
				));

			case 'person_date_created':
				return new Terms\FilterUserDateCreated($op, $options);

			case 'person_label':
				$labels = @$options['labels'] ?: array();
				if (!is_array($labels)) {
					$labels = array($labels);
				}

				return new Terms\FilterUserLabels($op, array(
					'labels' => $labels
				));

			case 'person_contact_phone':
				return new Terms\FilterUserContactPhone($op, $options);

			case 'person_contact_address':
				return new Terms\FilterUserContactAddress($op, $options);

			case 'person_contact_im':
				return new Terms\FilterUserContactIm($op, $options);

			case 'org_name':
				return new Terms\FilterOrgName($op, $options);

			case 'org_date_created':
				return new Terms\FilterOrgDateCreated($op, $options);

			case 'org_email_domain':
				return new Terms\FilterOrgEmailDomain($op, array(
					'domain' => @$options['email_domain'] ?: ''
				));

			case 'org_label':
				$labels = @$options['labels'] ?: array();
				if (!is_array($labels)) {
					$labels = array($labels);
				}

				return new Terms\FilterOrgLabels($op, array(
					'labels' => $labels
				));

			case 'org_contact_phone':
				return new Terms\FilterOrgContactPhone($op, $options);

			case 'org_contact_address':
				return new Terms\FilterOrgContactAddress($op, $options);

			case 'org_contact_im':
				return new Terms\FilterOrgContactIm($op, $options);

			case 'ticket_field':
				$new_opts = array();
				$new_opts['field_id'] = $type_id;
				$new_opts['value'] = isset($options['custom_fields']["field_{$type_id}"]) ? $options['custom_fields']["field_{$type_id}"] : null;
				return new Terms\FilterTicketField($op, $new_opts);

			case 'person_field':
				$new_opts = array();
				$new_opts['field_id'] = $type_id;
				$new_opts['value'] = isset($options['custom_fields']["field_{$type_id}"]) ? $options['custom_fields']["field_{$type_id}"] : null;
				return new Terms\FilterUserField($op, $new_opts);

			case 'org_field':
				$new_opts = array();
				$new_opts['field_id'] = $type_id;
				$new_opts['value'] = isset($options['custom_fields']["field_{$type_id}"]) ? $options['custom_fields']["field_{$type_id}"] : null;
				return new Terms\FilterOrgField($op, $new_opts);
		}

		return null;
	}
}