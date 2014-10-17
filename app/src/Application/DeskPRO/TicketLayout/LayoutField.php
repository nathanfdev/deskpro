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
 * @category Entities
 */

namespace Application\DeskPRO\TicketLayout;

use Application\DeskPRO\TicketLayout\Terms;
use Orb\Util\OptionsArray;

class LayoutField implements \Serializable
{
	const VIEW_ALWAYS = 'always';
	const VIEW_VALUE  = 'value';

	/**
	 * @var string
	 */
	private $field_type;

	/**
	 * @var int
	 */
	private $field_id = null;

	/**
	 * @var bool
	 */
	private $on_newticket = true;

	/**
	 * @var bool
	 */
	private $on_viewticket = true;

	/**
	 * @var string
	 */
	private $on_viewticket_mode = self::VIEW_VALUE;

	/**
	 * @var bool
	 */
	private $on_editticket = true;

	/**
	 * @var \Application\DeskPRO\TicketLayout\LayoutFieldCriteria
	 */
	private $criteria = null;


	/**
	 * @param string    $field_type
	 * @param int|null  $field_id
	 */
	public function __construct($field_type, $field_id = null)
	{
		$this->field_type = $field_type;
		$this->field_id   = $field_id;
	}


	/**
	 * @param array $options
	 */
	public function setOptionsFromArray(array $options)
	{
		$options = new OptionsArray($options);

		if ($options->get('on_editticket')) {
			$this->enableOnEdit();
		} else {
			$this->disableOnEdit();
		}

		if ($options->get('on_viewticket')) {
			$this->enableOnView($options->get('on_viewticket_mode', 'VALUE'));
		} else {
			$this->disableOnView();
		}

		if ($options->get('on_newticket')) {
			$this->enableOnNew();
		} else {
			$this->disableOnNew();
		}

		if ($options->has('criteria')) {
			$crit_options = new OptionsArray($options->get('criteria'));
			$criteria = new LayoutFieldCriteria();
			$criteria->setMode($crit_options->get('mode') == 'all' ? 'all' : 'any');

			foreach ($crit_options->get('terms') as $term_info) {
				$term = null;
				switch ($term_info['type']) {
					case 'CheckDepartment':
						if (!empty($term_info['options']['department_ids'])) {
							$term = new Terms\CheckDepartment($term_info['op'], $term_info['options']);
						}
						break;
					case 'CheckProduct':
						if (!empty($term_info['options']['product_ids'])) {
							$term = new Terms\CheckProduct($term_info['op'], $term_info['options']);
						}
						break;
					case 'CheckCategory':
						if (!empty($term_info['options']['category_ids'])) {
							$term = new Terms\CheckCategory($term_info['op'], $term_info['options']);
						}
						break;
					case 'CheckPriority':
						if (!empty($term_info['options']['priority_ids'])) {
							$term = new Terms\CheckPriority($term_info['op'], $term_info['options']);
						}
						break;
					case 'CheckWorkflow':
						if (!empty($term_info['options']['priority_ids'])) {
							$term = new Terms\CheckWorkflow($term_info['op'], $term_info['options']);
						}
						break;
				}

				if ($term) {
					$criteria->addTerm($term);
				}
			}

			if (count($criteria->getTerms())) {
				$this->setCriteria($criteria);
			}
		}
	}


	/**
	 * Get an ident string for the field type (a combination of the field type and field id).
	 *
	 * @return string
	 */
	public function getId()
	{
		$ident = $this->field_type;
		if ($this->field_id) {
			$ident .= '_' . $this->field_id;
		}
		return $ident;
	}


	/**
	 * @return string
	 */
	public function getFieldType()
	{
		return $this->field_type;
	}


	/**
	 * @return int|null
	 */
	public function getFieldId()
	{
		return $this->field_id;
	}


	/**
	 * Enables field on new ticket
	 */
	public function enableOnNew()
	{
		$this->on_newticket = true;
	}


	/**
	 * Disables field on new ticket
	 */
	public function disableOnNew()
	{
		$this->on_newticket = false;
	}


	/**
	 * @return bool
	 */
	public function isVisibleOnNew()
	{
		return $this->on_newticket;
	}


	/**
	 * Enables field on ticket view
	 *
	 * @param string $mode  View mode: Always show field or only show when there is a value
	 */
	public function enableOnView($mode = self::VIEW_VALUE)
	{
		$this->on_viewticket      = true;
		$this->on_viewticket_mode = ($mode == self::VIEW_VALUE ? self::VIEW_VALUE : self::VIEW_ALWAYS);
	}


	/**
	 * Disables field on ticket view
	 */
	public function disableOnView()
	{
		$this->on_viewticket      = null;
		$this->on_viewticket_mode = null;
	}


	/**
	 * @return bool
	 */
	public function isVisibleOnView()
	{
		return $this->on_viewticket;
	}


	/**
	 * @return bool
	 */
	public function isVisibleOnViewAlways()
	{
		return $this->on_viewticket_mode == self::VIEW_ALWAYS;
	}


	/**
	 * Enable field on ticket edit
	 */
	public function enableOnEdit()
	{
		$this->on_editticket = true;
	}


	/**
	 * Disable field on ticket edit
	 */
	public function disableOnEdit()
	{
		$this->on_editticket = false;
	}


	/**
	 * @return bool
	 */
	public function isVisibleOnEdit()
	{
		return $this->on_editticket;
	}


	/**
	 * True if there is a criteria object and that object has at least one term.
	 *
	 * @return bool
	 */
	public function hasCriteria()
	{
		if ($this->criteria && count($this->criteria) > 0) {
			return true;
		}

		return false;
	}


	/**
	 * @return LayoutFieldCriteria
	 */
	public function getCriteria()
	{
		return $this->criteria;
	}


	/**
	 * @param LayoutFieldCriteria $criteria
	 */
	public function setCriteria(LayoutFieldCriteria $criteria)
	{
		$this->criteria = $criteria;
	}


	/**
	 * Removes criteria from the field
	 */
	public function removeCriteria()
	{
		$this->criteria = null;
	}


	/**
	 * @return string
	 */
	public function compileJsCheck()
	{
		if (!$this->criteria || !$this->criteria->getTerms()) {
			return "function() { return true; }";
		}

		return $this->criteria->compileJsCheck();
	}


	/**
	 * @return array
	 */
	public function exportToArray()
	{
		$data = array();

		$data['version']  = 1;
		$data['field_type'] = $this->field_type;
		$data['field_id']   = $this->field_id;
		$data['options']    = array();

		if ($this->criteria) {
			$data['options']['criteria'] = $this->criteria->exportToArray();
		} else {
			$data['options']['criteria'] = null;
		}

		foreach (array(
			'on_newticket',
			'on_viewticket',
			'on_viewticket_mode',
			'on_editticket'
		) as $prop) {
			$data['options'][$prop] = $this->$prop;
		}

		return $data;
	}


	/**
	 * @return string
	 */
	public function exportToJson()
	{
		return json_encode($this->exportToArray());
	}


	/**
	 * @param array $data
	 */
	public function importFromArray(array $data)
	{
		$this->setOptionsFromArray($data['options']);
	}


	/**
	 * @return string
	 */
	public function serialize()
	{
		return $this->exportToJson();
	}


	/**
	 * @param string $data
	 */
	public function unserialize($data)
	{
		$data = json_decode($data, true);

		$this->__construct($data['field_type'], $data['field_id']);
		$this->importFromArray($data);
	}
}