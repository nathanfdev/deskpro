<?php

namespace Application\ImportBundle\Generator\Logger;

use Application\DeskPRO\Entity\DataStore;
use Application\DeskPRO\ORM\EntityManager;
use Monolog\Handler\AbstractProcessingHandler;
use Orb\Util\Strings;

class ImporterHandler extends AbstractProcessingHandler
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var DataStore
     */
    protected $importer;

    /**
     * @var int
     */
    protected $last_time = 0;

    /**
     * @var int
     */
    protected $count = 0;

    /**
     * @param DataStore $importer
     * @param EntityManager $em
     */
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
        $this->em->persist($this->importer);
        $this->em->flush($this->importer);
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        $log = $this->importer->getData('log').(string)$record['formatted'];
        if (isset($log[300000])) {
            $log = substr($log, -300000);
            $log = substr($log, strpos($log, "\n"));
            $log = trim($log);
        }

        $this->importer->setData('log', $log);
        $this->em->persist($this->importer);

        if ($this->last_time - time() > 1 || $this->count++ % 5 === 0 || !$this->importer->getId()) {
            $this->em->flush($this->importer);
        }

        if (!($log_file = $this->importer->getData('logfile'))) {
            $log_file = dp_get_log_dir().'/importlog-'.date('Ymd-His').'-'.Strings::random(6, Strings::CHARS_ALPHA_IU);
            $this->importer->setData('logfile', $log_file);
            $this->em->persist($this->importer);
            $this->em->flush($this->importer);
        }

        @file_put_contents($log_file, $record['formatted'], FILE_APPEND);
    }
}
