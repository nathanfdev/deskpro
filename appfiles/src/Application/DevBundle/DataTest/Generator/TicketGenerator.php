<?php

namespace Application\DevBundle\DataTest\Generator;

use \Orb\Util\Strings;

class TicketGenerator implements GeneratorInterface
{
	protected $start_date = 0;
	protected $end_date = 0;
	
	protected function __construct($start_date = 0, $end_date = 0)
	{
		$this->start_date = $start_date;
		$this->end_date = $end_date;
		
		if (!$this->start_date) $this->start_date = strtotime('2008-01-01');
		if (!$this->end_date) $this->end_date = strtotime('2010-12-12');
	}
	
	public function generateData($count)
	{
		$ret = array();
		
		while ($count-- > 0) {
			$ticket = array(
				'subject' => Strings::randomPronouncable(5) . Strings::randomPronouncable(mt_rand(2, 10)),
				'start_date' => mt_rand($this->start_date, $this->end_date),
			);
			
			$ret[] = $ticket;
		}
		
		return $ret;
	}
}
