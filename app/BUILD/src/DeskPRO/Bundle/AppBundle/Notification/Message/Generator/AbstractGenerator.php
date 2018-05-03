<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class AbstractGenerator.
 */
abstract class AbstractGenerator implements MessageGeneratorInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var TokenStorageInterface
     */
    protected $token_storage;

    /**
     * Constructor.
     *
     * @param EntityManager         $em
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(EntityManager $em, TokenStorageInterface $tokenStorage)
    {
        $this->em            = $em;
        $this->token_storage = $tokenStorage;
    }

    /**
     * @param SystemEventInterface $event
     *
     * @return MessageInterface[]
     */
    abstract public function createMessages(SystemEventInterface $event);

    /**
     * @param SystemEventInterface $event
     *
     * @return bool
     */
    abstract public function canCreateMessage(SystemEventInterface $event);

    /**
     * @return string
     */
    public function getType()
    {
        return get_called_class();
    }

    /**
     * @return Person
     */
    public function getUser()
    {
        if ($this->token_storage->getToken()) {
            return $this->token_storage->getToken()->getUser();
        }
        // todo actually this is just a stub to handle
        $user = new PersonGuest();

        return $user->setIsAgent(true)->setName('System');
    }
}
