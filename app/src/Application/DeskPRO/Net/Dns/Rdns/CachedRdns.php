<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
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
     * @return string|null
     * @throws \RuntimeException
     */
    public function lookup($ip)
    {
        $clean_ip = preg_replace('#[^0-9\.:]#', '', trim($ip));
        if (!$clean_ip || $clean_ip != $ip) {
            return '';
        }

        $key = $this->genCacheId($ip);
        $exist = $this->em->getRepository('DeskPRO:Cache')->load($key);
        if ($exist) {
            if ($exist === 'fail') return null;
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
     * @return string
     */
    private function genCacheId($ip)
    {
        return "ip2host--" . $ip;
    }
}