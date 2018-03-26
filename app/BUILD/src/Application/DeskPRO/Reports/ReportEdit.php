<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Reports;

use Doctrine\ORM\EntityManager;

class ReportEdit
{
    /**
     * @var \Application\DeskPRO\Entity\ReportBuilder
     */
    public $report;

    public function __construct($report)
    {
        $this->report = $report;
    }

    /**
     * @param EntityManager $em
     */
    public function save(EntityManager $em)
    {
        $em->persist($this->report);
        $em->flush();
    }
}
