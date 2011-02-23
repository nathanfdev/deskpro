<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Entity;

use \Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

use \Application\DeskPRO\Entity;

/**
 * Rules to show/hide various fields based on things.
 *
 * @orm:Entity
 * @orm:Table(name="department_display_rule")
 */
class DepartmentDisplayRule extends \Application\DeskPRO\Domain\DomainObject
{
	const COND_OP_IS = 'is';
	const COND_OP_NOT = 'not';
	const COND_FIELD_CATEGORY = 'category_id';
	const COND_FIELD_PRODUCT = 'product_id';
	const COND_FIELD_PRIORITY = 'priority_id';
	const COND_FIELD_WORKFLOW = 'workflow_id';

	const ACT_SHOW_FIELD = 'show';
	const ACT_HIDE_FIELD = 'hide';
	const ACT_STOP = 'stop';

	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Department
	 * @orm:ManyToOne(targetEntity="Department")
	 * @orm:JoinColumn(name="department_id", referencedColumnName="id")
	 */
	protected $department = null;

	/**
	 * An array of checks to run to see if this rule matches
	 *
	 * @var array
	 * @orm:Column(name="conds", type="array")
	 */
	protected $conds = array();

	/**
	 * An array of actions to run when the rule matches
	 *
	 * @var array
	 * @orm:Column(name="actions", type="array")
	 */
	protected $actions = array();

	/**
	 * @var int
	 * @orm:Column(name="run_order", type="integer")
	 */
	protected $run_order = 0;


	/**
	 * Check a ticket against this rule to see if it matches
	 *
	 * @param  $ticket
	 * @return bool
	 */
	public function isTicketMatch(Ticket $ticket)
	{
		#------------------------------
		# Get fields we'll test against
		#------------------------------

		$department_id = $ticket['department_id'];
		if ($department_id) {
			$department_id = App::getEntityRepository('DeskPRO:Department')->getIdsInTree($department_id, true);
		}

		$category_id = $ticket['category_id'];
		if ($category_id) {
			$category_id = App::getEntityRepository('DeskPRO:TicketCategory')->getIdsInTree($category_id, true);
		} else {
			$category_id = 0;
		}

		$product_id = $ticket['product_id'];


		#------------------------------
		# Check each condition
		#------------------------------

		$cond = App::getEntityRepository('DeskPRO:Department')->getIdsInTree($this->department['id'], true);
		if (!Arrays::isIn($ticket['department_id'], $cond)) {
			return false;
		}

		foreach ($this->conds as $field => $cond_ids) {
			switch ($field) {
				case 'category_id':
					$ids = array();
					foreach ($cond_ids as $cond) {
						$ids = array_merge($ids, App::getEntityRepository('DeskPRO:TicketCategory')->getIdsInTree($cond, true));
					}
					if (!Arrays::isIn($ticket['category_id'], $ids)) return false;
					break;
				case 'product_id':
					if (!Arrays::isIn($ticket['product_id'], $cond_ids)) return false;
					break;
			}
		}

		return true;
	}



	/**
	 * 'Compile' this rule into a javascript function that can be applied form the client.
	 *
	 * @return string
	 */
	public function compileToJavascript()
	{
		$js = array();
		$js[] = 'function(ticket) {';

		$cond = App::getEntityRepository('DeskPRO:Department')->getIdsInTree($this->department['id'], true);
		$cond = Arrays::castToType($cond, 'int');

		$js[] = "if (typeof ticket.department_id === 'function') ticket.department_id = ticket.department_id();";
		$js[] = "if ([" . implode(',', $cond) . "].indexOf(parseInt(ticket.department_id)) === -1) return false;";

		foreach ($this->conds as $field => $cond_ids) {
			switch ($field) {
				case 'category_id':
					$ids = array();
					foreach ($cond_ids as $cond) {
						$ids = array_merge($ids, App::getEntityRepository('DeskPRO:TicketCategory')->getIdsInTree($cond, true));
					}
					$ids = Arrays::castToType($ids, 'int');
						$js[] = "if (typeof ticket.category_id === 'function') ticket.category_id = ticket.category_id();";
					$js[] = "if ([" . implode(',', $ids) . "].indexOf(parseInt(ticket.category_id)) === -1) return false;";
					break;
				case 'product_id':
					$ids = Arrays::castToType($cond_ids, 'int');
						$js[] = "if (typeof ticket.product_id === 'function') ticket.product_id = ticket.product_id();";
					$js[] = "if ([" . implode(',', $ids) . "].indexOf(parseInt(ticket.product_id)) === -1) return false;";
					break;
				case 'priority_id':
					$ids = Arrays::castToType($cond_ids, 'int');
						$js[] = "if (typeof ticket.priority_id === 'function') ticket.priority_id = ticket.priority_id();";
					$js[] = "if ([" . implode(',', $ids) . "].indexOf(parseInt(ticket.priority_id)) === -1) return false;";
					break;
				case 'workflow_id':
					$ids = Arrays::castToType($cond_ids, 'int');
						$js[] = "if (typeof ticket.workflow_id === 'function') ticket.workflow_id = ticket.workflow_id();";
					$js[] = "if ([" . implode(',', $ids) . "].indexOf(parseInt(ticket.workflow_id)) === -1) return false;";
					break;
			}
		}

		$js[] = "var actions = [];";

		foreach ($this->actions as $action) {
			$js[] = "actions.push(" . json_encode($action) .");";
		}

		$js[] = "return actions;";
		$js[] = "}";

		$js = implode("", $js);

		// Shorten
		$js = str_replace('actions', 'a', $js);
		$js = str_replace('ticket.', 't.', $js);
		$js = str_replace('function(ticket)', 'function(t)', $js);

		return $js;
	}



	public function addCategoryCondition($category)
	{
		if (is_object($category)) {
			$category = $category['id'];
		}

		if (!isset($this->conds['category_id'])) {
			$this->conds['category_id'] = array();
		}

		$this->conds['category_id'][] = $category;
	}

	public function addProductCondition($product)
	{
		if (is_object($product)) {
			$product = $product['id'];
		}

		if (isset($this->conds['product_id'])) {
			$this->conds['product_id'] = array();
		}

		$this->conds['product_id'][] = $product;
	}

	public function addHideFieldAction($field)
	{
		if (is_object($field)) {
			$field = $field['id'];
		}

		$this->actions[] = array('hide', (int)$field);
	}

	public function addShowFieldAction($field)
	{
		if (is_object($field)) {
			$field = $field['id'];
		}

		$this->actions[] = array('show', (int)$field);
	}

	public function addStopRulesAction()
	{
		$this->actions[] = array('stop_rules');
	}
}