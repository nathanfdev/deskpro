<?php


namespace Application\DevBundle\DataTest\DataSet;

class Basic
{
	/**
	 * How many people should be generated
	 * @return int
	 */
	public function getNumPeople()
	{
		//return 50000;
		return 20000;
	}



	/**
	 * Minimum number of tickets.
	 *
	 * @return it
	 */
	public function getMinNumTickets()
	{
		//return 750000;
		return 350000;
	}


	public function getNumPriorities()
	{
		return 10;
	}

	public function getNumLangs()
	{
		return 4;
	}

	public function getAdditionalUsergroups()
	{
		return 3;
	}



	/**
	 * Percent chance that a person will have how many emails on their account
	 *
	 * @return array
	 */
	public function getNumEmailsPerPerson()
	{
		return array(
			array(7, 1),
			array(1, 0),
			array(1, 2),
			array(1, 3),
		);
	}


	/**
	 * Percent chance that a person whill have how many tickets on their account.
	 *
	 * Note that all users will be processed with this and at the end, will be repeated randomly
	 * until getMinNumTickets() is reached.
	 *
	 * @return array
	 */
	public function getNumTicketsPerPerson()
	{
		return array(
			array(7, 3),
			array(1, 1),
			array(1, 4),
			array(1, 5),
		);
	}


	/**
	 * Get the percent chance a ticket has additional participants
	 *
	 * @return array
	 */
	public function getParticipantsPerTicket()
	{
		return array(
			array(5, 0),
			array(4, 1),
			array(1, 2),
		);
	}



	/**
	 * To immitate certain users being more "popular" as participants, you can
	 * define a range being more likely to be added as a participant using a chance
	 * array. The ranges should be within 1-getNumPeople()
	 *
	 * @return array
	 */
	public function getParticipantPersonChance()
	{
		return array(
			array(5, array(1, $this->getNumTechs())),
			array(4, array(1, 5000)),
			array(1, array(1000, 2000)),
		);
	}



	/**
	 * Get how many companies we should create
	 *
	 * @return int
	 */
	public function getNumCompanies()
	{
		return 150;
	}

	public function getNumProducts()
	{
		return 5;
	}

	public function getNumCategoriesPerDep()
	{
		return 4;
	}



	/**
	 * Get the chance of being in a particular company
	 *
	 * @return int
	 */
	public function getCompanyPerPerson()
	{
		// percent => array(rangestart, rangeend), where the company is a random one in that range
		return array(
			array(3, null),
			array(5, array(1, 50)),
			array(1, array(51, 90)),
			array(1, array(111, 150)),
		);
	}



	/**
	 * Get a simple array of custom fields. These aren't real definitions, just
	 * simple definitions we'll use to fill the data table.
	 *
	 * @return array
	 */
	public function getTicketFields()
	{
		return array(
			array(1, array('type' => 'int', 'range' => array(1, 15))),
			array(2, array('type' => 'int', 'range' => array(1, 5))),
			array(3, array('type' => 'text')),
			array(1000, array('type' => 'choice', 'range' => array(1, 8), 'max_choices' => 1)),
			array(2000, array('type' => 'choice', 'range' => array(1, 50), 'max_choices' => 1)),
		);
	}



	/**
	 * Get an array and chances of an email domain being used.
	 *
	 * @return array
	 */
	public function getEmailDomains()
	{
		return array(
			array(1, 'gmail.com'),
			array(1, 'yahoo.com'),
			array(1, 'msn.com'),
			array(1, 'aol.com'),
			array(1, 'fastmai.fm'),
			array(1, 'something.edu'),
			array(4, ''), // random
		);
	}



	/**
	 * Get the date in which a ticket or user was created
	 *
	 * @return array
	 */
	public function getStartDate()
	{
		return array(
			array(8, array(strtotime('2008-01-01'), time())),
			array(1, array(strtotime('2009-01-01'), strtotime('2009-04-01'))),
			array(1, array(strtotime('2010-01-01'), strtotime('2009-01-30'))),
		);
	}



	/**
	 * Get an array of languages and chances
	 *
	 * @return array
	 */
	public function getPersonLanguage()
	{
		return array(
			array(8, 1),
			array(1, 2),
			array(1, 3),
		);
	}



	/**
	 * Get an array of department id choices/chances
	 * @return array
	 */
	public function getDepartmentIdChoices()
	{
		return array(
			array(3, 1),
			array(4, 2),
			array(1, 3),
			array(1, 4),
		);
	}

	
	public $dep_cat_ids = array();
	/**
	 * Get an array of category ID choices/chances
	 *
	 * @return array
	 */
	public function getCategoryIdChoices($department_id)
	{
		$ret = array();

		if (isset($this->dep_cat_ids[$department_id])) {
			foreach ($this->dep_cat_ids[$department_id] as $cid) {
				$ret[] = array(1, $cid);
			}
		} else {
			$ret[] = array(1,1);
			$ret[] = array(1,2);
		}

		return $ret;
	}


	/**
	 * How many techs there are. The first X users will be marked as techs.
	 *
	 * @return array
	 */
	public function getNumTechs()
	{
		return 15;
	}


	
	/**
	 * Get an array of chances for a particular choice
	 * 
	 * @return array
	 */
	public function getStatusChoices()
	{
		return array(
			array(1, 'awaiting_tech'), // 0.1% of 300,000 is 300 tickets
			array(10, 'pending'),
			array(200, 'resolved'),
			array(50, 'hidden'),
			array(800, 'closed'), // remaining are closed
		);
	}



	/**
	 * Get a word we can use in a subject
	 * @return array
	 */
	public function getCommonSubjectWord()
	{
		return array(
			array(1, 'saw'),
			array(1, 'golden'),
			array(1, 'cat'),
			array(1, 'water'),
			array(1, 'futon'),
			array(1, 'table'),
			array(995, '')
		);
	}


	public function getWords()
	{
		return array('piete','anowed','movice','eduse','kent','appeased','hist','ince',
		'marger','shorks','horwart','trit','vallow','negram','milem','worthod','yous',
		'untrit','neemen','amoup','knomint','daing','extere','abovern','incles','patil',
		'rety','amought','shod','prontly','sude','earrit','worsts','leture','alow','hostic',
		'requal','inst','thervity','otheort','fougher','imaths','watept','lable','prol','greass',
		'colic','rese','anot','watudy','thres','dire','eard','thining','pubject','almous','peratee',
		'offic','fundard','moducts','hanner','brial','houth','buite','tothire','head',
		'deths','arother','chown','cole','preaving','tothems','brient','gresed','sinnity',
		'sear','rivats','audio','ress','paps','decent','issard','mety','evict','meen',
		'casenly','livide','triath','cons','exced','amounish','bronsity','earls','peral',
		'wesic','exish','invols','chury','wain','matil','almon','appece','neir','desic',
		'ordio','lable','beive','sock','lange','grocal','chard','anyths','neve','nign',
		'strive','avelf','dearts','sect','nort','westil','leading','remed','dety','yearned',
		'strol','strit','shod','latil','physts','accome','cenglar','unducts','with','takelf',
		'dain','physed','enger','adder','reparty','parlic','espith','grol','necome','fedical',
		'offect','sume','themond','shous','aread','livers','devenge','propmes','lear','prol',
		'huse','beyont','pring','frit','belf','deass','wout','gird','polow','woult','alwars',
		'appare','nather','wheir','inday','cords','marls','horth','have','cound','heaters',
		'hisic','litired','operit','coutudy','exped','thincous','tries','stry','rember',
		'wallity','accort','wroble','conts','thernmes','ture','cone','medy','desped',
		'actil','inds','clown','shountry','whans','pical','asket');
	}
}