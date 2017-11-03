<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
