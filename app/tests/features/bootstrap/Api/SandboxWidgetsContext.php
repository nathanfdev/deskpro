<?php

namespace DpBehat\Api;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use DeskPRO\Bundle\AppBundle\Entity\SandboxWidget;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
