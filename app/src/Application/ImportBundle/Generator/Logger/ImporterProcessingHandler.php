<?php

namespace Application\ImportBundle\Generator\Logger;

use Application\DeskPRO\Entity\DataStore;
use Doctrine\ORM\EntityManager;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Orb\Util\Strings;

/**
 * Class ImporterProcessingHandler
 * @package Application\ImportBundle\Generator\Logger
 */
class ImporterProcessingHandler extends AbstractProcessingHandler
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
     * Constructor
     *
     * @param DataStore     $importer
     * @param EntityManager $em
     * @param int           $level
     * @param bool          $bubble
     */
    public function __construct(DataStore $importer, EntityManager $em, $level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->em       = $em;
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
        $str = (string)$record['formatted'];

        $str = str_replace(DP_WEB_ROOT, '', $str);
        $str = str_replace(dp_get_data_dir(), '/DP_DATA', $str);

        $log = $this->importer->getData('log').$str;
        if (isset($log[300000])) {
            $log = substr($log, strpos($log, "\n", strlen($log) - 300000));
            $log = trim($log);
        }

        $this->importer->setData('log', $log);
        $this->importer->setData('updated', time());

        if ($this->last_time - time() > 1 || $this->count++ % 5 === 0 || !$this->importer->getId()) {
            $this->em->flush($this->importer);
        }
    }
}
