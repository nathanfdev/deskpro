<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Net\Dns\Rdns;

use Doctrine\ORM\EntityManager;

class CachedRdns implements RdnsInterface
{
    /**
     * @var RdnsInterface
     */
    private $rdns;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var int
     */
    private $cache_life = 3600;

    /**
     * @param EntityManager $em
     * @param RdnsInterface $rdns
     * @param int           $cache_life
     */
    public function __construct(EntityManager $em, RdnsInterface $rdns, $cache_life = 3600)
    {
        $this->em   = $em;
        $this->rdns = $rdns;
    }

    /**
     * @param string $ip
     *
     * @throws \RuntimeException
     *
     * @return string|null
     */
    public function lookup($ip)
    {
        $clean_ip = preg_replace('#[^0-9\.:]#', '', trim($ip));
        if (!$clean_ip || $clean_ip != $ip) {
            return '';
        }

        $key   = $this->genCacheId($ip);
        $exist = $this->em->getRepository('DeskPRO:Cache')->load($key);
        if ($exist) {
            if ($exist === 'fail') {
                return;
            }

            return $exist;
        }

        $exception = null;
        try {
            $val = $this->rdns->lookup($ip);
        } catch (\Exception $e) {
            $exception = $e;
        }

        if ($exception || !$val) {
            $this->em->getRepository('DeskPRO:Cache')->save($key, 'fail', $this->cache_life);
        } else {
            $this->em->getRepository('DeskPRO:Cache')->save($key, $val, $this->cache_life);
        }

        if ($exception) {
            throw $e;
        }

        return $val;
    }

    /**
     * @param string $ip
     *
     * @return string
     */
    private function genCacheId($ip)
    {
        return 'ip2host--'.$ip;
    }
}
