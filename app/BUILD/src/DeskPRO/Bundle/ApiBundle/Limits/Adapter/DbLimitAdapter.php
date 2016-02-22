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

namespace DeskPRO\Bundle\ApiBundle\Limits\Adapter;

use Application\DeskPRO\Entity\ApiKey;
use DeskPRO\Bundle\ApiBundle\Limits\Model\GlobalLimit;
use DeskPRO\Bundle\ApiBundle\Limits\Model\KeyLimit;
use DeskPRO\Bundle\ApiBundle\Limits\Model\LimitInterface;
use DeskPRO\Bundle\AppBundle\Entity\ApiKeyLimit;
use Doctrine\ORM\EntityManager;

/**
 * Class DbLimitAdapter.
 */
class DbLimitAdapter implements LimitAdapterInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var \SplObjectStorage
     */
    protected $global_limits;

    /**
     * @var \SplObjectStorage
     */
    protected $key_limits;

    /**
     * DbLimitAdapter constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em            = $em;
        $this->global_limits = new \SplObjectStorage();
        $this->key_limits    = new \SplObjectStorage();
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobalLimits()
    {
        $db_global_limits = $this->repo()->findBy(['type' => LimitInterface::TYPE_GLOBAL]);
        foreach ($db_global_limits as $db_limit) {
            $this->global_limits->attach($this->getLimit($db_limit), $db_limit);
        }

        return $this->global_limits;
    }

    /**
     * {@inheritdoc}
     */
    public function getKeyLimits(ApiKey $key)
    {
        $db_key_limits = $this->repo()->findBy(['type' => LimitInterface::TYPE_KEY, 'api_key' => $key]);
        foreach ($db_key_limits as $db_limit) {
            $this->key_limits->attach($this->getLimit($db_limit), $db_limit);
        }

        return $this->key_limits;
    }

    /**
     * {@inheritdoc}
     */
    public function saveGlobalLimit(LimitInterface $limit)
    {
        $this->saveLimit($limit);
    }

    /**
     * {@inheritdoc}
     */
    public function saveKeyLimit(LimitInterface $limit, $key)
    {
        $this->saveLimit($limit);
    }

    /**
     * @param LimitInterface $limit
     */
    protected function saveLimit(LimitInterface $limit)
    {
        if ($limit->getType() === LimitInterface::TYPE_GLOBAL) {
            $storage = $this->global_limits;
        } else {
            $storage = $this->key_limits;
        }

        $db_limit = $storage->offsetGet($limit);
        /* @var ApiKeyLimit $db_limit */
        $db_limit
            ->setCurrent($limit->getCurrentLimit())
            ->setStartTime($limit->getStartTime());

        $this->em->persist($db_limit);
    }

    /**
     * @param ApiKeyLimit $db_limit
     *
     * @return GlobalLimit|KeyLimit
     */
    protected function getLimit(ApiKeyLimit $db_limit)
    {
        switch ($db_limit->getType()) {
            case LimitInterface::TYPE_GLOBAL:
                $limit = new GlobalLimit();
                break;
            case LimitInterface::TYPE_KEY;
                $limit = new KeyLimit();
                break;
            default:
                throw new \LogicException(
                    sprintf(
                        'Unknown limit type [ %s ], expecting one of [ %s ]',
                        $db_limit->getType(),
                        implode(',', [LimitInterface::TYPE_KEY, LimitInterface::TYPE_GLOBAL]))
                );
        }

        !$db_limit->getStartTime() ? $db_limit->setStartTime(new \DateTime()) : null;

        $limit
            ->setInterval(\DateInterval::createFromDateString($db_limit->getInterval().' seconds'))
            ->setStartTime($db_limit->getStartTime())
            ->setCurrent($db_limit->getCurrent())
            ->setLimit($db_limit->getLimit());

        return $limit;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function repo()
    {
        return $this->em->getRepository('\DeskPRO\Bundle\AppBundle\Entity\ApiKeyLimit');
    }

    public function __destruct()
    {
        $flush = [];
        $this->global_limits->rewind();
        $this->key_limits->rewind();
        while ($this->global_limits->current()) {
            $flush[] = $this->global_limits->getInfo();
            $this->global_limits->next();
        }
        while ($this->key_limits->current()) {
            $flush[] = $this->key_limits->getInfo();
            $this->key_limits->next();
        }
        $this->em->flush($flush);
    }
}
