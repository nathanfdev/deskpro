<?php

namespace DeskPRO\Bundle\AppBundle\Limits\Adapter;

use Application\DeskPRO\Entity\ApiKey;
use DeskPRO\Bundle\AppBundle\Entity\ApiKeyLimit;
use DeskPRO\Bundle\AppBundle\Limits\Model\AbstractLimit;
use DeskPRO\Bundle\AppBundle\Limits\Model\GlobalLimit;
use DeskPRO\Bundle\AppBundle\Limits\Model\KeyLimit;
use DeskPRO\Bundle\AppBundle\Limits\Model\LimitInterface;
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
        $db_global_limits = $this->repo()->findBy(['limit_type' => AbstractLimit::TYPE_GLOBAL]);
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
        $db_key_limits = $this->repo()->findBy(['limit_type' => AbstractLimit::TYPE_KEY, 'api_key' => $key]);
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
        if (!$this->key_limits->offsetExists($limit)) {
            $this->createKeyLimit($key, $limit);
        } else {
            $this->saveLimit($limit);
        }
    }

    protected function createKeyLimit(ApiKey $key, LimitInterface $limit)
    {
        $db_limit = new ApiKeyLimit();
        $db_limit
            ->setInterval($limit->getIntervalInSeconds())
            ->setCurrent($limit->getCurrentLimit())
            ->setLimit($limit->getLimit())
            ->setApiKey($key)
            ->setType($limit->getType());
        $this->persistAndFlush($db_limit);
    }

    /**
     * @param LimitInterface $limit
     */
    protected function saveLimit(LimitInterface $limit)
    {
        if ($limit->getType() === AbstractLimit::TYPE_GLOBAL) {
            $storage = $this->global_limits;
        } else {
            $storage = $this->key_limits;
        }

        $db_limit = $storage->offsetGet($limit);
        /* @var ApiKeyLimit $db_limit */
        $db_limit
            ->setCurrent($limit->getCurrentLimit())
            ->setLimit($limit->getLimit())
            ->setInterval($limit->getIntervalInSeconds())
            ->setStartTime($limit->getStartTime());

        $this->persistAndFlush($db_limit);
    }

    protected function persistAndFlush(ApiKeyLimit $db_limit)
    {
        $this->em->persist($db_limit);
        $this->em->flush($db_limit);
    }

    /**
     * @param ApiKeyLimit $db_limit
     *
     * @return GlobalLimit|KeyLimit
     */
    protected function getLimit(ApiKeyLimit $db_limit)
    {
        switch ($db_limit->getType()) {
            case AbstractLimit::TYPE_GLOBAL:
                $limit = new GlobalLimit();
                break;
            case AbstractLimit::TYPE_KEY:
                $limit = new KeyLimit();
                break;
            default:
                throw new \LogicException(
                    sprintf(
                        'Unknown limit type [ %s ], expecting one of [ %s ]',
                        $db_limit->getType(),
                        implode(',', [AbstractLimit::TYPE_KEY, AbstractLimit::TYPE_GLOBAL]))
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
}
