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
 */

namespace Application\DeskPRO\Banning;

use Application\DeskPRO\Entity\BanEmail;
use Doctrine\ORM\EntityManager;

class EmailBans
{
	/**
	 * @var \Application\DeskPRO\ORM\EntityManager
	 */

	protected $em;

    /**
     * @var \Application\DeskPRO\Entity\BanEmail[]
     */

    protected $email_bans;

	/**
	 * @var int
	 */

	protected $per_page = 20;

	/**
	 * @var int
	 */

	protected $from;

	/**
	 * @var string
	 */

	protected $search_phrase;

	/**
	 * @param EntityManager $em
	 */

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	/**
	 * @param int $per_page
	 *
	 * @return $this
	 */

	public function setPerPage($per_page)
	{
		$this->per_page = $per_page;

		return $this;
	}

	/**
	 * @param int $page
	 *
	 * @return $this
	 */

	public function setPage($page)
	{
		if ($page == 0) {

			$page = 1;
		}

		$this->from = ($page - 1) * $this->per_page;

		return $this;
	}

	/**
	 * @param string $search_phrase
	 */

	public function setSearchPhrase($search_phrase)
	{
		$this->search_phrase = $search_phrase;
	}

	/**
	 * Loads twitter accounts data from the database
	 */

	private function preload()
	{
		if ($this->email_bans !== null) {

			return;
		}

		$this->email_bans = $this->em->getRepository('DeskPRO:BanEmail')->getList(
			$this->from,
			$this->per_page,
			$this->search_phrase
		);
	}


	/**
	 * Resets this repository so the next time data is requested form it, it will
	 * be queried again.
	 */

	public function reset()
	{
		$this->email_bans = null;
	}

	/**
	 * @param int $id
	 * @return \Application\DeskPRO\Entity\BanEmail
	 */

	public function getById($id)
	{
		return $this->em->getRepository('DeskPRO:BanEmail')->get($id);
	}

    /**
     * @return \Application\DeskPRO\Entity\BanEmail[]
     */

    public function getAll()
    {
        $this->preload();

        return $this->email_bans;
    }

	/**
	 * @return array
	 */

	public function getAllAsNestedArray()
	{
		$this->preload();

		$result = array();

		foreach ($this->email_bans as $email_ban) {

			$result[] = array('banned_email' => $email_ban);
		}

		return $result;
	}

	/**
	 * @return int
	 */

	public function getPageCount()
	{
		return $this->em->getRepository('DeskPRO:BanEmail')->getPageCount($this->per_page, $this->search_phrase);
	}

	/**
	 * @return int
	 */
	public function getCount()
	{
		return $this->em->getRepository('DeskPRO:BanEmail')->getCount($this->search_phrase);
	}

	/**
	 * @return int
	 */

	public function count()
	{
		$this->preload();

		return count($this->email_bans);
	}

	/**
	 * @return BanEmail
	 */

	public function createNew()
	{
		return BanEmail::createEmailBan();
	}
}