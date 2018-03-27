<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

class ChatBlock extends AbstractEntityRepository
{
    /**
     * How long a block stays in place.
     */
    const BLOCK_TIMEOUT = 86400;

    public function getBlockForVisitor($visitor_id = null, $ip = null)
    {
        if (!$visitor_id && !$ip) {
            return null;
        }

        $qb = $this->createQueryBuilder('b');

        if ($visitor_id) {
            $qb->orWhere('b.visitor_id = :vid')->setParameter('vid', $visitor_id);
        }

        if ($ip) {
            $qb->orWhere('b.ip_address = :ip')->setParameter('ip', $ip);
        }

        $datecut = new \DateTime('-'.self::BLOCK_TIMEOUT.' seconds');
        $qb->andWhere('b.date_created > :created')->setParameter('created', $datecut);
        $block = $qb->getQuery()->setMaxResults(1)->getOneOrNullResult();

        return $block;
    }

    /**
     * @param string $ip_address
     *
     * @return \Application\DeskPRO\Entity\ChatBlock
     */
    public function getBlockForIp($ip_address)
    {
        $datecut = new \DateTime('-'.self::BLOCK_TIMEOUT.' seconds');

        $block = $this->_em->createQuery('
            SELECT b
            FROM DeskPRO:ChatBlock b
            WHERE b.ip_address = ?0 AND b.date_created > ?1
        ')->setParameters([$ip_address, $datecut])->setMaxResults(1)->getOneOrNullResult();

        return $block;
    }

    /**
     * @param string $ip_address
     * @param string $visitor_id
     *
     * @return bool
     */
    public function isBlocked($ip_address, $visitor_id = 0)
    {
        $datecut = new \DateTime('-'.self::BLOCK_TIMEOUT.' seconds');
        $blocked = $this->_em->getConnection()->fetchColumn('
            SELECT id FROM chat_blocks
            WHERE (visitor_id = ? OR ip_address = ?) AND date_created > ?
        ', [$visitor_id, $ip_address, $datecut->format('Y-m-d H:i:s')]);

        return $blocked ? true : false;
    }

    /**
     * Deletes blocks older than BLOCK_TIMEOUT.
     *
     * @return int
     */
    public function cleanupBlocks()
    {
        $datecut = new \DateTime('-'.self::BLOCK_TIMEOUT.' seconds');
        $count   = $this->_em->getConnection()->executeUpdate('
            DELETE FROM chat_blocks
            WHERE date_created < ?
        ', [$datecut->format('Y-m-d H:i:s')]);

        return $count;
    }
}
