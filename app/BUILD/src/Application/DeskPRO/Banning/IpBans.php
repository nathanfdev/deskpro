<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Banning;

use Application\DeskPRO\Entity\BanIp;
use Doctrine\ORM\EntityManager;

class IpBans
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\Entity\BanIp[]
     */
    protected $ip_bans;

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
     * Loads ip bans data from the database.
     */
    private function preload()
    {
        if ($this->ip_bans !== null) {
            return;
        }

        $this->ip_bans = $this->em->getRepository('DeskPRO:BanIp')->getList(
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
        $this->ip_bans = null;
    }

    /**
     * @param int $id
     *
     * @return \Application\DeskPRO\Entity\BanIp
     */
    public function getById($id)
    {
        return $this->em->getRepository('DeskPRO:BanIp')->get($id);
    }

    /**
     * @return \Application\DeskPRO\Entity\BanIp[]
     */
    public function getAll()
    {
        $this->preload();

        return $this->ip_bans;
    }

    /**
     * @return array
     */
    public function getAllAsNestedArray()
    {
        $this->preload();

        return $this->ip_bans;
    }

    /**
     * @return int
     */
    public function getPageCount()
    {
        return $this->em->getRepository('DeskPRO:BanIp')->getPageCount($this->per_page, $this->search_phrase);
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->em->getRepository('DeskPRO:BanIp')->getCount($this->search_phrase);
    }

    /**
     * @return int
     */
    public function count()
    {
        $this->preload();

        return count($this->ip_bans);
    }

    /**
     * @return BanIp
     */
    public function createNew()
    {
        return BanIp::createBanIp();
    }
}
