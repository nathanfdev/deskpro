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
        if ($this->token_storage->getToken() && $this->token_storage->getToken()->getUser() instanceof Person) {
            return $this->token_storage->getToken()->getUser();
        }

        $user = new PersonGuest();

        $isSystem = !(bool) $this->token_storage->getToken();

        return $user->setIsAgent($isSystem)->setName($isSystem ? 'System' : '');
    }
}
