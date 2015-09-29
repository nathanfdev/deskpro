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

namespace DpBehat\Api;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use DeskPRO\Bundle\AppBundle\Entity\SandboxWidget;
use Doctrine\ORM\EntityManager;

/**
 * Defines application features from the specific context.
 */
class SandboxWidgetsContext implements SnippetAcceptingContext
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @Given there are :num sandbox widgets
     */
    public function iShouldBeAbleToLoadAnAdminUser($num)
    {
        foreach (range(1, $num) as $i) {
            $widget = new SandboxWidget();
            $widget->setName('widget'.$i);
            $widget->setInventory($i);
            $this->em->persist($widget);
        }

        $this->em->flush();
    }
}
