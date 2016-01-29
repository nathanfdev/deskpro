<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\ImportBundle\Logger;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\DataStore;
use Doctrine\ORM\EntityManager;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

/**
 * Class ImporterProcessingHandler.
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
     * Constructor.
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
        $str = (string) $record['formatted'];

        $str = str_replace(DP_WEB_ROOT, '', $str);
        $str = str_replace(App::$container->getParameter('kernel.dp_config_dir'), '/DP_DATA', $str);

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
