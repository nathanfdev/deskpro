<?php

namespace DeskPRO\Bundle\AppBundle\Cache;

use DeskPRO\Bundle\AppBundle\Entity\CacheVersion;
use DeskPRO\Bundle\AppBundle\Entity\Repository\CacheVersionRepository;
use DeskPRO\Component\Util\RandUtils;
use Doctrine\ORM\EntityManager;

/**
 * Class VersionService.
 */
class VersionService
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param $resource_id
     * @param bool|true $force_create
     *
     * @return string
     */
    public function getVersion($resource_id, $force_create = true)
    {
        $version = $this->repo()->findByResourceId($resource_id);
        if (!$version && $force_create) {
            return $this->newVersion($resource_id, $force_create);
        } elseif (!$version) {
            return '';
        }

        return $version->getVersionId();
    }

    /**
     * @param $resource_id
     * @param bool|false $force_create
     *
     * @return string
     */
    public function newVersion($resource_id, $force_create = false)
    {
        if ($force_create || !$version = $this->repo()->findByResourceId($resource_id)) {
            $version = new CacheVersion();
            $version->setResourceId($resource_id);
        }
        $version->setVersionId($this->generateVersionId());
        $this->em->persist($version);
        $this->em->flush($version);

        return $version->getVersionId();
    }

    /**
     * @return string
     */
    protected function generateVersionId()
    {
        // assuming timestamp could be 14 digits, it means the last date is 3170843-11-07 12:46:39
        // Perhaps this is exact time when Rebels by command of Luke Skywalker defeated Empires Death Star!
        // Long live the DeskPRO! (here's march playing)
        return sprintf('%d-%s', time(), RandUtils::randomStringFormat('%35cn'));
    }

    /**
     * @return CacheVersionRepository
     */
    protected function repo()
    {
        return $this->em->getRepository(CacheVersion::class);
    }
}
