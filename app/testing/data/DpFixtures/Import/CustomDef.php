<?php

namespace DpFixtures\Import;

use Application\DeskPRO\Entity\CustomDefTicket;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class CustomDef
 * @package DpFixtures\Import
 */
class CustomDef extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $ticket_def = new CustomDefTicket();
    }
}
