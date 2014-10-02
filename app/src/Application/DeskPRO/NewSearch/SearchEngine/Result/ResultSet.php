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

namespace Application\DeskPRO\NewSearch\SearchEngine\Result;

use Application\DeskPRO\Entity;

class ResultSet
{
	/**
	 * @var int
	 */
	private $total;

	/**
	 * @var array
	 */
	private $results;


	/**
	 * @param array $results
	 * @param int $total
	 */
	public function __construct($results = array(), $total = null)
	{
		$this->results = $results;

		if ($total === null) {
			$this->total = count($results);
		} else {
			$this->total = $total;
		}
	}


	/**
	 * @return mixed
	 */
	public function getTotal()
	{
		return $this->total;
	}


	/**
	 * @return mixed
	 */
	public function getResults()
	{
		return $this->results;
	}


	/**
	 * @return array
	 */
	public function getTypedResults()
	{
		$res = array();

		foreach ($this->results as $r) {
			if ($r instanceof Entity\Article) {
				$type = 'article';
			} elseif ($r instanceof Entity\News) {
				$type = 'news';
			} elseif ($r instanceof Entity\Download) {
				$type = 'download';
			} elseif ($r instanceof Entity\Feedback) {
				$type = 'feedback';
			} elseif ($r instanceof Entity\Ticket) {
				$type = 'ticket';
			} elseif ($r instanceof Entity\Person) {
				$type = 'person';
			} elseif ($r instanceof Entity\ChatConversation) {
				$type = 'chat_conversation';
			}

			$res[] = array('type' => $type, 'object' => $r);
		}

		return $res;
	}
}