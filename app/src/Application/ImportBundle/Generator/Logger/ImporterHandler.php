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
     * @param DataStore     $importer
     * @param EntityManager $em
     */
    public function __construct(DataStore $importer, EntityManager $em)
    {
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
        $log = $this->importer->getData('log').(string) $record['formatted'];
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
