<?php

namespace Application\ImportBundle\Generator\Logger;

use Application\DeskPRO\Entity\DataStore;
use Application\DeskPRO\ORM\EntityManager;
use Monolog\Handler\AbstractProcessingHandler;

class ImporterHandler extends AbstractProcessingHandler
{
    protected $em;

    protected $importer;

    public function __construct(DataStore $importer, EntityManager $em)
    {
        $this->em = $em;
        $this->importer = $importer;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->em->flush($this->importer);
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        $this->importer->setData('log', $this->importer->getData('log').(string)$record['formatted']);
        $this->em->flush($this->importer);
    }
}
