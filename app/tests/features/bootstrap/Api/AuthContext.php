<?php

namespace DpBehat\Api;

use Application\DeskPRO\Entity\Session;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Tester\Exception\PendingException;
use DeskPRO\Bundle\AppBundle\Entity\SandboxWidget;
use Doctrine\ORM\EntityManager;
use DpTests\TestBundle\UserDetailsRepo;
use Orb\Util\Util;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Defines application features from the specific context.
 */
class AuthContext implements SnippetAcceptingContext
{
    /**
     * @var EntityManager
     */
    private $em;
    /**
     * @var UserDetailsRepo
     */
    private $user_details;

    public function __construct(EntityManager $em, UserDetailsRepo $user_details)
    {
        $this->em = $em;
        $this->user_details = $user_details;
    }

    /**
     * @Given the agent session auth :session_id is valid for :who
     */
    public function theAgentSessionIsValidForPerson($session_id, $who)
    {
        $user = $this->user_details->getWho($who);

        session_start();
        $_SESSION['_sf2_attributes'] = array('auth_person_id' => $user->getId());
        $data = session_encode();
        unset($_SESSION['_sf2_attributes']);
        session_destroy();

        $session = new Session();
        $session->setAuth($session_id);
        $session->setData($data);
        $session->setPerson($user);

        $this->em->persist($session);
        $this->em->flush();
    }
}
