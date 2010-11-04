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
		return 50000;
	}



	/**
	 * Minimum number of tickets.
	 *
	 * @return it
	 */
	public function getMinNumTickets()
	{
		return 750000;
	}



	/**
	 * Percent chance that a person will have how many emails on their account
	 *
	 * @return array
	 */
	public function getNumEmailsPerPerson()
	{
		return array(
			array(70, 1),
			array(15, 0),
			array(10, 2),
			array(5, 3),
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
			array(70, 3),
			array(10, 1),
			array(10, 4),
			array(10, 5),
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
			array(80, 0),
			array(10, 1),
			array(10, 2),
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
			array(50, array(1, $this->getNumTechs())),
			array(40, array(1, 5000)),
			array(10, array(1000, 2000)),
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



	/**
	 * Get the chance of being in a particular company
	 *
	 * @return int
	 */
	public function getCompanyPerPerson()
	{
		// percent => array(rangestart, rangeend), where the company is a random one in that range
		return array(
			array(30, null),
			array(50, array(1, 50)),
			array(10, array(51, 90)),
			array(10, array(111, 150)),
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
			array(4, array('type' => 'text')),
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
			array(10, 'gmail.com'),
			array(10, 'yahoo.com'),
			array(10, 'msn.com'),
			array(10, 'aol.com'),
			array(10, 'fastmai.fm'),
			array(10, 'something.edu'),
			array(40, ''), // random
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
			array(80, array(strtotime('2008-01-01'), time())),
			array(10, array(strtotime('2009-01-01'), strtotime('2009-04-01'))),
			array(10, array(strtotime('2010-01-01'), strtotime('2009-01-30'))),
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
			array(80, 1),
			array(10, 2),
			array(10, 3),
		);
	}



	/**
	 * Get an array of department id choices/chances
	 * @return array
	 */
	public function getDepartmentIdChoices()
	{
		return array(
			array(50, 1),
			array(40, 2),
			array(10, 3),
		);
	}

	

	/**
	 * Get an array of category ID choices/chances
	 *
	 * @return array
	 */
	public function getCategoryIdChoices()
	{
		return array(
			array(20, 1),
			array(20, 2),
			array(20, 3),
			array(30, 4),
			array(10, 5),
		);
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
			array(1, array('open', 'awaiting_tech')), // 0.1% of 300,000 is 300 tickets
			array(10, array('open', 'awaiting_user')),
			array(50, array('hidden', 'spam')),
			array(939, array('closed', null)), // remaining are closed
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
}