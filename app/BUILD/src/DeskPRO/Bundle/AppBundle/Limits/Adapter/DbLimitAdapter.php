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
        $this->settingsResolver = $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobalLimits()
    {
        $globalSettings = $this->settingsResolver->getGlobalSettings();
        $limitSettings  = [
            AbstractLimit::INTERVAL_DAY  => $globalSettings->get('api_limits.global.day'),
            AbstractLimit::INTERVAL_HOUR => $globalSettings->get('api_limits.global.hour'),
        ];

        $limits = [];
        foreach ($limitSettings as $interval => $value) {
            $dbLimit = $this->getOrCreateGlobalLimitInDb($value, $interval);
            if ($dbLimit) {
                $limits[] = $this->createLimitModel($dbLimit);
            }
        }

        return $limits;
    }

    /**
     * {@inheritdoc}
     */
    public function getKeyLimits(ApiKey $key)
    {
        $keyLimits = $this->em->getRepository(ApiKeyLimit::class)->findBy([
            'limit_type' => AbstractLimit::TYPE_KEY,
            'api_key'    => $key,
        ]);

        $limits = [];
        foreach ($keyLimits as $dbLimit) {
            $limits[] = $this->createLimitModel($dbLimit);
        }

        return $limits;
    }

    /**
     * {@inheritdoc}
     */
    public function saveGlobalLimit(LimitInterface $limit)
    {
        $dbLimit = $this->getOrCreateGlobalLimitInDb($limit->getLimit(), $limit->getIntervalInSeconds());
        if (!$dbLimit) {
            return;
        }

        $dbLimit->setCurrent($limit->getCurrentLimit());
        $dbLimit->setStartTime($limit->getStartTime());

        $this->em->persist($dbLimit);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function saveKeyLimit(LimitInterface $limit, $key)
    {
        $dbLimit = $this->getOrCreateKeyLimitInDb($key, $limit->getLimit(), $limit->getIntervalInSeconds());
        if (!$dbLimit) {
            return;
        }

        $dbLimit->setCurrent($limit->getCurrentLimit());
        $dbLimit->setStartTime($limit->getStartTime());

        $this->em->persist($dbLimit);
        $this->em->flush();
    }

    /**
     * @param ApiKeyLimit $dbLimit
     *
     * @return GlobalLimit|KeyLimit
     */
    private function createLimitModel(ApiKeyLimit $dbLimit)
    {
        if ($dbLimit->getType() === AbstractLimit::TYPE_GLOBAL) {
            $limit = new GlobalLimit();
        } elseif ($dbLimit->getType() === AbstractLimit::TYPE_KEY) {
            $limit = new KeyLimit();
        } else {
            throw new \LogicException(
                sprintf(
                    'Unknown limit type [ %s ], expecting one of [ %s ]',
                    $dbLimit->getType(),
                    implode(',', [AbstractLimit::TYPE_KEY, AbstractLimit::TYPE_GLOBAL]))
            );
        }

        if (!$dbLimit->getStartTime()) {
            $dbLimit->setStartTime(new \DateTime());
        }

        $limit
            ->setInterval(\DateInterval::createFromDateString($dbLimit->getInterval().' seconds'))
            ->setStartTime($dbLimit->getStartTime())
            ->setCurrent($dbLimit->getCurrent())
            ->setLimit($dbLimit->getLimit())
        ;

        return $limit;
    }

    /**
     * @param int $value
     * @param int $interval
     *
     * @return ApiKeyLimit|null
     */
    private function getOrCreateGlobalLimitInDb($value, $interval)
    {
        if ($value <= 0) {
            return;
        }

        $dbLimit = $this->em->getRepository(ApiKeyLimit::class)->findOneBy([
            'limit_type'    => AbstractLimit::TYPE_GLOBAL,
            'time_interval' => $interval,
        ]);

        if (!$dbLimit) {
            $dbLimit = new ApiKeyLimit();
            $dbLimit
                ->setInterval($interval)
                ->setCurrent($value)
                ->setLimit($value)
                ->setType(AbstractLimit::TYPE_GLOBAL)
                ->setStartTime(new \DateTime())
            ;

            $this->em->persist($dbLimit);
            $this->em->flush();
        } elseif ($dbLimit->getLimit() !== $value) {
            $dbLimit->setLimit($value);

            $this->em->persist($dbLimit);
            $this->em->flush();
        }

        return $dbLimit;
    }

    /**
     * @param ApiKey $key
     * @param int    $value
     * @param int    $interval
     *
     * @return ApiKeyLimit|null
     */
    private function getOrCreateKeyLimitInDb(ApiKey $key, $value, $interval)
    {
        if ($value <= 0) {
            return;
        }

        $dbLimit = $this->em->getRepository(ApiKeyLimit::class)->findOneBy([
            'limit_type'    => AbstractLimit::TYPE_KEY,
            'time_interval' => $interval,
            'api_key'       => $key,
        ]);

        if (!$dbLimit) {
            $dbLimit = new ApiKeyLimit();
            $dbLimit
                ->setInterval($interval)
                ->setCurrent($value)
                ->setLimit($value)
                ->setApiKey($key)
                ->setType(AbstractLimit::TYPE_KEY)
                ->setStartTime(new \DateTime())
            ;

            $this->em->persist($dbLimit);
            $this->em->flush();
        } elseif ($dbLimit->getLimit() !== $value) {
            $dbLimit->setLimit($value);

            $this->em->persist($dbLimit);
            $this->em->flush();
        }

        return $dbLimit;
    }
}
