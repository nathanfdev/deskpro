<?php

namespace DeskPRO\Bundle\AppBundle\Limits\Adapter;

use Application\DeskPRO\Entity\ApiKey;
use Application\DeskPRO\NewSettings\SettingsResolver;
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
    protected $globalLimits;

    /**
     * @var \SplObjectStorage
     */
    protected $keyLimits;

    /**
     * @var SettingsResolver
     */
    protected $settingsResolver;

    /**
     * DbLimitAdapter constructor.
     *
     * @param EntityManager    $em
     * @param SettingsResolver $resolver
     */
    public function __construct(EntityManager $em, SettingsResolver $resolver)
    {
        $this->em               = $em;
        $this->globalLimits     = new \SplObjectStorage();
        $this->keyLimits        = new \SplObjectStorage();
        $this->settingsResolver = $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobalLimits()
    {
        $dbGlobalLimits = $this->getLimitsFromSettings();
        foreach ($dbGlobalLimits as $dbLimit) {
            $this->globalLimits->attach($this->getLimit($dbLimit), $dbLimit);
        }

        return $this->globalLimits;
    }

    /**
     * {@inheritdoc}
     */
    public function getKeyLimits(ApiKey $key)
    {
        $dbKeyLimits = $this->repo()->findBy(['limit_type' => AbstractLimit::TYPE_KEY, 'api_key' => $key]);
        foreach ($dbKeyLimits as $dbLimit) {
            $this->keyLimits->attach($this->getLimit($dbLimit), $dbLimit);
        }

        return $this->keyLimits;
    }

    protected function getLimitsFromSettings()
    {
        $limits   = [];
        $dayLimit = $this->settingsResolver->getGlobalSettings()->get('api_limits.global.day');
        if ($dayLimit && $dayLimit !== -1) {
            $dbDayLimit = $this->repo()->findOneBy([
                'limit_type'    => AbstractLimit::TYPE_GLOBAL,
                'time_interval' => AbstractLimit::INTERVAL_DAY,
            ]);
            if (!$dbDayLimit) {
                $dbDayLimit = new ApiKeyLimit();
                $dbDayLimit
                    ->setInterval(AbstractLimit::INTERVAL_DAY)
                    ->setCurrent($dayLimit)
                    ->setLimit($dayLimit)
                    ->setType(AbstractLimit::TYPE_GLOBAL);
                $this->persistAndFlush($dbDayLimit);
            }
            $dbDayLimit->setLimit($dayLimit);
            $limits[] = $dbDayLimit;
        }
        $hourLimit = $this->settingsResolver->getGlobalSettings()->get('api_limits.global.hour');
        if ($hourLimit && $hourLimit !== -1) {
            $dbHourLimit = $this->repo()->findOneBy([
                'limit_type'    => AbstractLimit::TYPE_GLOBAL,
                'time_interval' => AbstractLimit::INTERVAL_HOUR,
            ]);
            if (!$dbHourLimit) {
                $dbHourLimit = new ApiKeyLimit();
                $dbHourLimit
                    ->setInterval(AbstractLimit::INTERVAL_HOUR)
                    ->setCurrent($hourLimit)
                    ->setLimit($hourLimit)
                    ->setType(AbstractLimit::TYPE_GLOBAL);
                $this->persistAndFlush($dbHourLimit);
            }
            $dbHourLimit->setLimit($hourLimit);
            $limits[] = $dbHourLimit;
        }

        return $limits;
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
        if (!$this->keyLimits->offsetExists($limit)) {
            $this->createKeyLimit($key, $limit);
        } else {
            $this->saveLimit($limit);
        }
    }

    protected function createKeyLimit(ApiKey $key, LimitInterface $limit)
    {
        $dbLimit = new ApiKeyLimit();
        $dbLimit
            ->setInterval($limit->getIntervalInSeconds())
            ->setCurrent($limit->getCurrentLimit())
            ->setLimit($limit->getLimit())
            ->setApiKey($key)
            ->setType($limit->getType());
        $this->persistAndFlush($dbLimit);
    }

    /**
     * @param LimitInterface $limit
     */
    protected function saveLimit(LimitInterface $limit)
    {
        if ($limit->getType() === AbstractLimit::TYPE_GLOBAL) {
            $storage = $this->globalLimits;
        } else {
            $storage = $this->keyLimits;
        }

        $dbLimit = $storage->offsetGet($limit);
        /* @var ApiKeyLimit $dbLimit */
        $dbLimit
            ->setCurrent($limit->getCurrentLimit())
            ->setLimit($limit->getLimit())
            ->setInterval($limit->getIntervalInSeconds())
            ->setStartTime($limit->getStartTime());

        $this->persistAndFlush($dbLimit);
    }

    protected function persistAndFlush(ApiKeyLimit $dbLimit)
    {
        $this->em->persist($dbLimit);
        $this->em->flush($dbLimit);
    }

    /**
     * @param ApiKeyLimit $dbLimit
     *
     * @return GlobalLimit|KeyLimit
     */
    protected function getLimit(ApiKeyLimit $dbLimit)
    {
        switch ($dbLimit->getType()) {
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
                        $dbLimit->getType(),
                        implode(',', [AbstractLimit::TYPE_KEY, AbstractLimit::TYPE_GLOBAL]))
                );
        }

        !$dbLimit->getStartTime() ? $dbLimit->setStartTime(new \DateTime()) : null;

        $limit
            ->setInterval(\DateInterval::createFromDateString($dbLimit->getInterval().' seconds'))
            ->setStartTime($dbLimit->getStartTime())
            ->setCurrent($dbLimit->getCurrent())
            ->setLimit($dbLimit->getLimit());

        return $limit;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function repo()
    {
        return $this->em->getRepository(ApiKeyLimit::class);
    }
}
