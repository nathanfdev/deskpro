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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\ActionEngine\Services;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\TicketManager;
use DeskPRO\Bundle\AppBundle\ActionEngine\Interfaces\EnvironmentServiceAwareInterface;
use DeskPRO\Bundle\AppBundle\ActionEngine\Interfaces\TicketManagerAwareInterface;
use DeskPRO\Bundle\AppBundle\ActionEngine\Interfaces\TokenStorageAwareInterface;
use DeskPRO\Bundle\AppBundle\ActionEngine\Interfaces\ValidatorAwareInterface;
use DeskPRO\Bundle\AppBundle\DependencyInjection\SystemServices\EnvironmentService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\RecursiveValidator;

class TicketApplicatorService extends AbstractApplicatorService
{
    protected $class     = Ticket::class;
    protected $namespace = 'Tickets';

    /** @var  EntityManager */
    protected $em;
    /** @var  TicketManager */
    protected $tm;
    /** @var  TokenStorageInterface */
    protected $tokenStorage;
    /** @var  EnvironmentService */
    protected $environmentService;
    /** @var RecursiveValidator $validator */
    protected $validator;

    public function __construct(
        EntityManager $em,
        TicketManager $tm,
        TokenStorageInterface $tokenStorage,
        RecursiveValidator $validator,
        EnvironmentService $environmentService
    ) {
        parent::__construct($em);
        $this->tm                 = $tm;
        $this->tokenStorage       = $tokenStorage;
        $this->validator          = $validator;
        $this->environmentService = $environmentService;
    }

    protected function createApplicator($class)
    {
        $applicator = new $class($this->em);
        if ($applicator instanceof TicketManagerAwareInterface) {
            $applicator->setTicketManager($this->tm);
        }
        if ($applicator instanceof ValidatorAwareInterface) {
            $applicator->setValidator($this->validator);
        }
        if ($applicator instanceof TokenStorageAwareInterface) {
            $applicator->setTokenStorage($this->tokenStorage);
        }
        if ($applicator instanceof EnvironmentServiceAwareInterface) {
            $applicator->setEnvironmentService($this->environmentService);
        }

        return $applicator;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntities(array $ids)
    {
        /** @var Ticket[] $entities */
        $entities = parent::getEntities($ids);
        foreach ($entities as $entity) {
            $entity->disableAutoTicketProcess();
        }

        return $entities;
    }
}
