<?php

namespace DeskPRO\Bundle\ApiBundle\Log\Finder;

use Doctrine\ORM\EntityManager;

class DbFinder implements FinderInterface
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function find($request_id)
    {
        return $this->em->getRepository('DeskPRO\Bundle\AppBundle\Entity\ApiLog')->findOneBy(['request_id' => $request_id]);
    }
}
