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

namespace DeskPRO\Bundle\AppBundle\Limits;

use Application\DeskPRO\Entity\ApiKey;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Limits\Adapter\LimitAdapterInterface;
use DeskPRO\Bundle\AppBundle\Limits\Exception\LimitExhaustedException;
use DeskPRO\Bundle\AppBundle\Limits\Model\AbstractLimit;
use DeskPRO\Bundle\AppBundle\Limits\Model\KeyLimit;
use DeskPRO\Bundle\AppBundle\Limits\Model\LimitInterface;
use DeskPRO\Bundle\AppBundle\Limits\Model\LimitSet;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class LimitsService.
 */
class LimitsService
{
    /**
     * @var \DeskPRO\Bundle\AppBundle\Limits\Model\LimitSet
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
     * @var \DeskPRO\Bundle\AppBundle\Limits\Adapter\LimitAdapterInterface
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
     * @param SettingsResolver                                               $resolver
     * @param TokenStorageInterface                                          $storage
     * @param \DeskPRO\Bundle\AppBundle\Limits\Adapter\LimitAdapterInterface $limit_adapter
     * @param EntityManager                                                  $em
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
        if ($key = $this->getKey()) { // I have no idea how to prevent multiple collecting when testing.
            foreach ($this->getKeyLimits($key) as $key_limit) {
                $this->limit_set->addLimit($key_limit);
            }
        }
    }

    /**
     * @param ApiKey $key
     *
     * @return \DeskPRO\Bundle\AppBundle\Limits\Model\LimitInterface[]
     */
    public function getKeyLimits(ApiKey $key)
    {
        return $this->limit_adapter->getKeyLimits($key);
    }

    public function saveLimit(ApiKey $key, LimitInterface $limit)
    {
        if ($limit->getType() === AbstractLimit::TYPE_GLOBAL) {
            $this->limit_adapter->saveGlobalLimit($limit);
        } elseif ($limit->getType() === AbstractLimit::TYPE_KEY) {
            $this->limit_adapter->saveKeyLimit($limit, $key);
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
     *
     */
    public function checkLimits()
    {
        foreach ($this->limit_set as $limit) {
            /** @var \DeskPRO\Bundle\AppBundle\Limits\Model\LimitInterface $limit */
            if (!$limit->replenish() && !$limit->hasLimit()) {
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
            /* @var \DeskPRO\Bundle\AppBundle\Limits\Model\LimitInterface $limit */
            $limit->reduceLimit();
            $this->saveLimit($this->getKey(), $limit);
        }
    }

    public function createLimit($interval = AbstractLimit::INTERVAL_HOUR)
    {
        switch ($interval) {
            case 3600:
                $limit_hit = $this->resolver->getGlobalSettings()->get('api_limits.key.hour');
                break;
            case 86400:
                $limit_hit = $this->resolver->getGlobalSettings()->get('api_limits.key.day');
                break;
            default:
                $default_limit = $this->resolver->getGlobalSettings()->get('api_limits.key.default', 0);
                $limit_hit     = $this->resolver->getGlobalSettings()->get('api_limits.key.day', $default_limit);
                break;
        }

        $limit = new KeyLimit();
        $limit
            ->setCurrent($limit_hit)
            ->setLimit($limit_hit)
            ->setInterval(new \DateInterval(sprintf('PT%dS', $interval)));

        return $limit;
    }
}
