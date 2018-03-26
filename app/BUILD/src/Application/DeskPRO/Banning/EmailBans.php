<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Banning;

use Application\DeskPRO\Entity\BanEmail;
use Doctrine\ORM\EntityManager;

class EmailBans
{
    /**
     * @var \Doctrine\ORM\EntityManager
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
     * filter wildcards only if true.
     *
     * @var bool
     */
    protected $wildcard = false;

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

    public function setWildcard($wildcard)
    {
        $this->wildcard = (bool) $wildcard;

        return $this;
    }

    /**
     * @param $search_phrase
     *
     * @return $this
     */
    public function setSearchPhrase($search_phrase)
    {
        $this->search_phrase = $search_phrase;

        return $this;
    }

    /**
     * Loads twitter accounts data from the database.
     */
    private function preload()
    {
        if ($this->email_bans !== null) {
            return;
        }

        $this->email_bans = $this->em->getRepository('DeskPRO:BanEmail')->getList(
            $this->from,
            $this->per_page,
            $this->search_phrase,
            $this->wildcard
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
     *
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

        $result = [];

        foreach ($this->email_bans as $email_ban) {
            $result[] = ['banned_email' => $email_ban];
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getPageCount()
    {
        return $this->em->getRepository('DeskPRO:BanEmail')->getPageCount($this->per_page, $this->search_phrase, $this->wildcard);
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->em->getRepository('DeskPRO:BanEmail')->getCount($this->search_phrase, $this->wildcard);
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
