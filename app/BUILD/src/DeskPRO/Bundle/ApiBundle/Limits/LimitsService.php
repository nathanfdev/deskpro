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

namespace DeskPRO\Bundle\ApiBundle\Limits;

use Application\DeskPRO\Entity\ApiKey;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\ApiBundle\Limits\Adapter\LimitAdapterInterface;
use DeskPRO\Bundle\ApiBundle\Limits\Exception\LimitExhaustedException;
use DeskPRO\Bundle\ApiBundle\Limits\Model\LimitInterface;
use DeskPRO\Bundle\ApiBundle\Limits\Model\LimitSet;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class LimitsService.
 */
class LimitsService
{
    /**
     * @var \DeskPRO\Bundle\ApiBundle\Limits\Model\LimitSet
     */
    protected $limit_set;
    /**
     * @var SettingsResolver
     */
    protected $resolver;

    /**
     * @var TokenStorageInterface
     */
    protected $storage;

    /**
     * @var LimitAdapterInterface
     */
    protected $limit_adapter;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ApiKey
     */
    protected $key;

    /**
     * LimitsService constructor.
     *
     * @param SettingsResolver      $resolver
     * @param TokenStorageInterface $storage
     * @param LimitAdapterInterface $limit_adapter
     * @param EntityManager         $em
     */
    public function __construct(
        SettingsResolver $resolver,
        TokenStorageInterface $storage,
        LimitAdapterInterface $limit_adapter,
        EntityManager $em
    ) {
        $this->limit_set     = new LimitSet();
        $this->resolver      = $resolver;
        $this->storage       = $storage;
        $this->limit_adapter = $limit_adapter;
        $this->em            = $em;
        $this->collectLimits();
    }

    /**
     * Collection all limits.
     */
    protected function collectLimits()
    {
        foreach ($this->limit_adapter->getGlobalLimits() as $global_limit) {
            $this->limit_set->addLimit($global_limit);
        }
        foreach ($this->limit_adapter->getKeyLimits($this->getKey()) as $key_limit) {
            $this->limit_set->addLimit($key_limit);
        }
    }

    /**
     * @return \Application\DeskPRO\Entity\ApiKey
     */
    protected function getKey()
    {
        if (!$this->key) {
            $credentials = $this->storage->getToken()->getCredentials();
            /** @var \Application\DeskPRO\EntityRepository\ApiKey $repository */
            $repository = $this->em->getRepository('\Application\DeskPRO\Entity\ApiKey');
            $this->key  = $repository->findByKeyString($credentials);
        }

        return $this->key;
    }

    /**
     * @param $controller
     * @param $action
     */
    public function checkLimits($controller, $action)
    {
        foreach ($this->limit_set as $limit) {
            /** @var LimitInterface $limit */
            if (!$limit->hasLimit() && !$limit->replenish()) {
                throw new LimitExhaustedException();
            }
        }
    }

    /**
     *
     */
    public function reduceLimits()
    {
        foreach ($this->limit_set as $limit) {
            /* @var \DeskPRO\Bundle\ApiBundle\Limits\Model\LimitInterface $limit */
            $limit->reduceLimit();
            if ($limit->getType() === LimitInterface::TYPE_GLOBAL) {
                $this->limit_adapter->saveGlobalLimit($limit);
            } else {
                $this->limit_adapter->saveKeyLimit($limit, $this->getKey());
            }
        }
    }
}
