<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\ServerFileCheck;

use Application\DeskPRO\Distribution\VerifyChecksums;
use Doctrine\ORM\EntityManager;

class ServerFileCheck
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\Distribution\VerifyChecksums
     */
    protected $verify;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;

        $this->verify = new VerifyChecksums();
    }

    /**
     * @return array
     */
    public function getCount()
    {
        return [
            'count' => $this->verify->countChunks(),
        ];
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getById($id)
    {
        return $this->verify->compareChunk($id);
    }
}
