<?php

namespace Application\DevBundle\DataTest\Generator;

use \Orb\Util\Strings;

class UserGenerator implements GeneratorInterface
{
	public function generateData($count)
	{
		$ret = array();
		
		while ($count-- > 0) {
			$user = array(
				'full_name' => Strings::randomPronouncable(5) . Strings::randomPronouncable(10),
				'username' => $this->_genUsername(),
				'emails' => $this->_genEmails(),
			);
			
			$ret[] = $user;
		}
		
		return $ret;
	}
	
	protected function _genUsername()
	{
		static $x = 0;
		
		if (mt_rand(0, 100) < 80) {
			return null;
		}
		
		return $x++ . Strings::randomPronouncable(5);
	}
	
	protected function _genEmails()
	{
		static $x = 0;
		$possible_counts = array(1, 1, 1, 1, 1, 2, 3, 4, 5, 6);
		$count = $possible_counts[array_rand($possible_counts)];
		
		$domains = array('gmail.com', 'googlemail.com', 'aol.com', 'yahoo.com', 'RAND');
		
		$ret = array();
		while ($count-- > 0) {
			$domain = $domains[array_rand($domains)];
			if ($domain == 'RAND') {
				$domain = Strings::randomPronouncable(mt_rand(2, 8)) . '.com';
			}
			
			$ret[] = Strings::randomPronouncable(mt_rand(2, 10)) . '@' . $domain;
		}
		
		return $ret;
	}
}