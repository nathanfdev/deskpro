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
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build\Helper201405;

use Application\DeskPRO\Entity\TicketLayout as TicketLayoutEntity;
use Application\DeskPRO\TicketLayout;

class LayoutUpgrader
{
	/**
	 * @var array
	 */
	private $form_new;

	/**
	 * @var array
	 */
	private $form_view;

	/**
	 * @var array
	 */
	private $form_edit;


	/**
	 * @param array $form_new
	 * @param array $form_view
	 * @param array $form_edit
	 */
	public function __construct(array $form_new, array $form_view, array $form_edit)
	{
		$this->form_new  = $form_new;
		$this->form_view = $form_view;
		$this->form_edit = $form_edit;
	}


	/**
	 * @return TicketLayoutEntity
	 */
	public function getTicketLayout()
	{
		$ticket_layout = new TicketLayoutEntity();
		$ticket_layout->is_enabled = true;
		$ticket_layout->user_layout = $this->getUserLayout();
		$ticket_layout->agent_layout = $this->getAgentLayout();
		return $ticket_layout;
	}


	/**
	 * @return TicketLayout\Layout
	 */
	private function getUserLayout()
	{
		$layout = new TicketLayout\Layout();

		foreach ($this->form_new as $old_field) {
			if (isset($old_field['agent_only']) && $old_field['agent_only']) {
				continue;
			}

			$field = $this->convertField($old_field);
			$field->enableOnNew();
			$layout->add($field);
		}
		foreach ($this->form_view as $old_field) {
			if (isset($old_field['agent_only']) && $old_field['agent_only']) {
				continue;
			}

			$field = $this->convertField($old_field);
			if ($layout->has($field->getId())) {
				$field = $layout->get($field->getId());
			} else {
				$layout->add($field);
			}

			$field->enableOnView();
		}
		foreach ($this->form_edit as $old_field) {
			if (isset($old_field['agent_only']) && $old_field['agent_only']) {
				continue;
			}

			$field = $this->convertField($old_field);
			if ($layout->has($field->getId())) {
				$field = $layout->get($field->getId());
			} else {
				$layout->add($field);
			}

			$field->enableOnEdit();
		}

		return $layout;
	}


	/**
	 * @return TicketLayout\Layout
	 */
	private function getAgentLayout()
	{
		$layout = new TicketLayout\Layout();

		foreach ($this->form_new as $old_field) {
			$field = $this->convertField($old_field);
			$field->enableOnNew();
			$layout->add($field);
		}
		foreach ($this->form_view as $old_field) {
			$field = $this->convertField($old_field);
			if ($layout->has($field->getId())) {
				$field = $layout->get($field->getId());
			} else {
				$layout->add($field);
			}

			$field->enableOnView();
		}
		foreach ($this->form_edit as $old_field) {
			$field = $this->convertField($old_field);
			if ($layout->has($field->getId())) {
				$field = $layout->get($field->getId());
			} else {
				$layout->add($field);
			}

			$field->enableOnEdit();
		}

		return $layout;
	}


	/**
	 * @param array $info
	 * @return TicketLayout\LayoutField
	 */
	private function convertField(array $info)
	{
		$field = $this->getFieldFromLegacyField($info);
		$crit = $this->getCriteriaFromLegacyField($info);
		if ($crit) {
			$field->setCriteria($crit);
		}

		// Default to all off
		$field->disableOnNew();
		$field->disableOnView();
		$field->disableOnEdit();

		return $field;
	}


	/**
	 * @param array $info
	 * @return TicketLayout\LayoutField
	 */
	private function getFieldFromLegacyField(array $info)
	{
		switch ($info['field_type']) {
			case 'person_name':
				return new TicketLayout\LayoutField('user_name');
			case 'ticket_subject':
				return new TicketLayout\LayoutField('subject');
			case 'message':
				return new TicketLayout\LayoutField('message');
			case 'attachments':
				return new TicketLayout\LayoutField('attach');
			case 'ticket_cc_emails':
				return new TicketLayout\LayoutField('cc');
			case 'captcha':
				return new TicketLayout\LayoutField('captcha');
			case 'ticket_department':
				return new TicketLayout\LayoutField('department');
			case 'ticket_category':
				return new TicketLayout\LayoutField('category');
			case 'ticket_priority':
				return new TicketLayout\LayoutField('priority');
			case 'ticket_workflow':
				return new TicketLayout\LayoutField('workflow');
			case 'ticket_product':
				return new TicketLayout\LayoutField('product');
			case 'ticket_field':
				return new TicketLayout\LayoutField('ticket_field', $info['field_id']);
			default:
				throw new \InvalidArgumentException("Unknown field type {$info['field_type']}");
		}
	}


	/**
	 * @param array $info
	 * @return TicketLayout\LayoutFieldCriteria|null
	 */
	private function getCriteriaFromLegacyField(array $info)
	{
		if (empty($info['rules'])) {
			return null;
		}

		$crit = new TicketLayout\LayoutFieldCriteria();

		if ($info['rule_match_type'] == 'all') {
			$crit->setMode(TicketLayout\LayoutFieldCriteria::CRIT_ALL);
		} else {
			$crit->setMode(TicketLayout\LayoutFieldCriteria::CRIT_ALL);
		}

		foreach ($info['rules'] as $rule) {
			$term = $this->getCriteriaTermFromLegacyTerm($rule);
			if ($term) {
				$crit->addTerm($term);
			}
		}

		if (!$crit->count()) {
			return null;
		}
		return $crit;
	}


	/**
	 * @param array $rule
	 * @return TicketLayout\Terms\AbstractTicketLayoutTerm|null
	 */
	private function getCriteriaTermFromLegacyTerm(array $rule)
	{
		switch ($rule['type']) {
			case 'category':
				if (empty($rule['options']['category'])) return null;
				$ids = is_array($rule['options']['category']) ? $rule['options']['category'] : array($rule['options']['category']);
				return new TicketLayout\Terms\CheckCategory($rule['op'], array('category_ids' => $ids));
			case 'priority':
				if (empty($rule['options']['priority'])) return null;
				$ids = is_array($rule['options']['priority']) ? $rule['options']['priority'] : array($rule['options']['priority']);
				return new TicketLayout\Terms\CheckPriority($rule['op'], array('priority_ids' => $ids));
			case 'workflow':
				if (empty($rule['options']['workflow'])) return null;
				$ids = is_array($rule['options']['workflow']) ? $rule['options']['workflow'] : array($rule['options']['workflow']);
				return new TicketLayout\Terms\CheckWorkflow($rule['op'], array('workflow_ids' => $ids));
			case 'product':
				if (empty($rule['options']['product'])) return null;
				$ids = is_array($rule['options']['product']) ? $rule['options']['product'] : array($rule['options']['product']);
				return new TicketLayout\Terms\CheckProduct($rule['op'], array('product_ids' => $ids));
			default:
				return null;
		}
	}
}