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
 * @category Entities
 */

namespace Application\DeskPRO\TicketLayout;

use Orb\Util\OptionsArray;

class LayoutField implements \Serializable
{
	const VIEW_ALWAYS = 'ALWAYS';
	const VIEW_VALUE  = 'VALUE';

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
			$data['criteria'] = $this->criteria->exportToArray();
		} else {
			$data['criteria'] = null;
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