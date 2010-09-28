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
class Date extends FieldGroup
{
	protected function init()
	{
		if (!$this->hasOption('year_min')) $this->setOption ('year_min', 1900);
		if (!$this->hasOption('year_max')) $this->setOption ('year_max', 2025);

		$this->_initChoices();
		$this->_initTransformers();
		// TODO add validation
		// TODO add localization
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
			array($this, 'transformDateToArray'),
			array($this, 'transformArrayToDate')
		);

		$this->addTransformer($trans);
	}

	public function transformDateToArray(\DateTime $date)
	{
		if (!$date) return null;
		
		$array = array(
			'year'  => $dateTime->format('Y'),
			'month' => $dateTime->format('m'),
			'day'   => $dateTime->format('d'),
		);

		return $array;
	}

	public function transformArrayToDate(array $array)
	{
		if (!$array) return null;

		$date = new \DateTime("{$array['year']}-{$array['month']}-{$array['day']}");

		return $date;
	}
}