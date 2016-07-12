<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\ImportBundle\Generator;

use Application\DeskPRO\Entity\DataStore;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Class ImporterProgressBar.
 */
class ImporterProgressBar extends ProgressBar
{
    protected $em;
    protected $importer;
    protected $total_count;

    /**
     * @param DataStore     $importer
     * @param EntityManager $em
     * @param int           $total_count
     */
    public function __construct(DataStore $importer, EntityManager $em, $total_count)
    {
        $this->em       = $em;
        $this->importer = $importer;
        parent::__construct(new NullOutput(), $total_count);
    }

    public function display()
    {
        $this->importer->setData('progress_step', $this->getStep());
        $this->importer->setData('progress_max', $this->getMaxSteps());
        $this->importer->setData('progress_start', $this->getStartTime());

        $this->em->flush($this->importer);
    }
}
