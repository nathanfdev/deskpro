<?php

namespace DeskPRO\Bundle\ApiBundle\Log\Serializer;

use DeskPRO\Bundle\AppBundle\Entity\ApiLog;
use Doctrine\ORM\EntityManager;

/**
 * Class SerializeSerializer.
 */
class SerializeSerializer implements SerializerInterface
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
     * @param ApiLog $log
     *
     * @return string
     */
    public function serialize(ApiLog $log)
    {
        $this->em->detach($log);

        return $log->getRequestId().'%%%'.serialize($log).PHP_EOL;
    }

    /**
     * @param $str
     *
     * @return mixed
     */
    public function unserialize($str)
    {
        $data       = explode('%%%', $str);
        $request_id = $data[0];
        /** @var ApiLog $logModel */
        $log = unserialize($data[1]);
        $this->em->merge($log);
    }
}
