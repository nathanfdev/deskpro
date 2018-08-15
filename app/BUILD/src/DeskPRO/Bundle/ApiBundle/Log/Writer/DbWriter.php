<?php

namespace DeskPRO\Bundle\ApiBundle\Log\Writer;

use DeskPRO\Bundle\AppBundle\Entity\ApiLog;
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
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function write(ApiLog $log)
    {
        $key = $log->getKey();
        $log->setKey(null);
        $this->em->persist($log);
        $this->em->flush();
        if ($key && $key->getId()) {
            $this->em->getConnection()->update('api_log', ['api_key_id' => $key->getId()], ['id' => $log->getId()]);
        }
    }
}
