<?php

namespace Application\DeskPRO\Profiler\DataCollector;

use Profiler\LiveBundle\Profiler\DataCollector\DoctrineDataCollector as BaseDoctrineDataCollector;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DoctrineDataCollector extends BaseDoctrineDataCollector
{
    public function __construct(RegistryInterface $registry, $logger = null)
    {
        parent::__construct($registry, $logger->getLogger('query_logger'));
    }   
}
