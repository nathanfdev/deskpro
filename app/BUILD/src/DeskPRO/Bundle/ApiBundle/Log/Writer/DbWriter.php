<?php

namespace DeskPRO\Bundle\ApiBundle\Log\Writer;

use DeskPRO\Bundle\AppBundle\Entity\ApiLog;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use Doctrine\ORM\EntityManager;

/**
 * Class DbWriter.
 */
class DbWriter implements WriterInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Doctrine
     */
    protected $doctrine;

    /**
     * @param EntityManager $em
     * @param Doctrine      $doctrine
     */
    public function __construct(EntityManager $em, Doctrine $doctrine)
    {
        $this->em       = $em;
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     */
    public function write(ApiLog $log)
    {
        $key = $log->getKey();
        $log->setKey(null);
        // this is an emergency case. We still need to log everything in DB even if it was DBAL exception.
        if (!$this->em->isOpen()) {
            $this->em = $this->doctrine->resetManager();
        }
        $this->em->persist($log);
        $this->em->flush();
        if ($key && $key->getId()) {
            $this->em->getConnection()->update('api_log', ['api_key_id' => $key->getId()], ['id' => $log->getId()]);
        }
    }
}
