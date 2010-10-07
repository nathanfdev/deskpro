<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Form
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Orb\Form\Field;

use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * A date field
 */
class Date extends CompositeField
{
	const DATA_FORMAT_DATETIME = 'DateTime';
	const DATA_FORMAT_TIMESTAMP = 'timestamp';
	const DATA_FORMAT_STRING = 'string';

	const INPUT_FORMAT_CHOICE = 'choice';
	const INPUT_FORMAT_STRING = 'string';


	protected function init()
	{
		if (!$this->hasOption('year_min')) $this->setOption ('year_min', 1900);
		if (!$this->hasOption('year_max')) $this->setOption ('year_max', 2025);

		if (!$this->hasOption('data_format'))  $this->setOption('data_format', self::DATA_FORMAT_DATETIME);
		if (!$this->hasOption('input_format')) $this->setOption('input_format', self::INPUT_FORMAT_STRING);

		if ($this->getOption('input_format') == self::INPUT_FORMAT_CHOICE) {
			$this->_initChoices();
		} else {
			$this->_initInputField();
		}
		
		$this->_initTransformers();
		// TODO add validation
		// TODO add localization
	}

	protected function _initInputField()
	{
		$f = new \Orb\Form\Field\Text(array('name' => 'date_input'));
		$this->addFeild('date_input');
	}

	protected function _initChoices()
	{
		// Year
		$f = new \Orb\Form\Field\Choice(array('name' => 'year'));
		foreach (range($this->getOption('year_min'), $this->getOption('year_max')) as $year) {
			$f->addChoice($year, $year);
		}
		$this->addField($f);

		// Month
		$f = new \Orb\Form\Field\Choice(array('name' => 'month'));
		foreach (range(1, 12) as $month) {
			$f->addChoice($month, $month);
		}
		$this->addField($f);

		// Day
		$f = new \Orb\Form\Field\Choice(array('name' => 'day'));
		foreach (range(1, 31) as $day) {
			$f->addChoice($day, $day);
		}
		$this->addField($f);
	}

	protected function _initTransformers()
	{
		$trans = new \Orb\Form\Transformer\Callback(
			array($this, 'transformToForm'),
			array($this, 'transformToData')
		);

		$this->addTransformer($trans);
	}

	public function transformToForm($data)
	{
		if (!$date) return null;

		switch ($this->getOption('data_format')) {
			case self::DATA_FORMAT_TIMESTAMP:
				$data = new \DateTime();
				$data->setTimestamp($data);
				break;

			case self::DATA_FORMAT_STRING:
				$data = \DateTime::createFromFormat('Y-m-d', $data);
				break;

		}
		
		switch ($this->getOption('input_format')) {
			case self::INPUT_FORMAT_CHOICE:
				$return = array(
					'year'  => $dateTime->format('Y'),
					'month' => $dateTime->format('m'),
					'day'   => $dateTime->format('d'),
				);
				break;
			
			case self::INPUT_FORMAT_STRING:
				$return = array('date_format' => $data->format('Y-m-d'));
				break;
		}		

		return $return;
	}

	public function transformArrayToDate($data)
	{
		if (!$data) return null;

		switch ($this->getOption('input_format')) {
			case self::INPUT_FORMAT_CHOICE:
				$date = new \DateTime("{$array['year']}-{$array['month']}-{$array['day']}");
				break;
			
			case self::INPUT_FORMAT_STRING:
				$date = new \DateTime($data['date_input']);
				break;
		}

		switch ($this->getOption('data_format')) {
			case self::DATA_FORMAT_TIMESTAMP:
				$return = $date->getTimestamp();
				break;

			case self::DATA_FORMAT_STRING:
				$return = $data->format('Y-m-d');
				break;

		}

		return $return;
	}
}