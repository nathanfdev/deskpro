<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Doctrine\ORM\EntityRepository;

/**
 * Class AppInstance.
 */
class AppInstance extends EntityRepository
{
    public function getInstanceByName($name)
    {
        return $this->createQueryBuilder('a')
            ->select('a')
            ->where('a.package = ?0')
            ->setParameter(0, $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param \Application\DeskPRO\Entity\AppInstance $app
     *
     * @return array
     */
    public function getPermissionsForInstance(\Application\DeskPRO\Entity\AppInstance $app)
    {
        $ret = ['usergroup_ids' => [], 'person_ids' => []];

        if ($app->perm_type != 'set') {
            return $ret;
        }

        $perms = $this->_em->getConnection()->fetchAll('SELECT * FROM app_instance_permissions WHERE app_instance_id = ?', [$app->id]);

        foreach ($perms as $p) {
            if ($p['usergroup_id']) {
                $ret['usergroup_ids'][] = (int) $p['usergroup_id'];
            } elseif ($p['person_id']) {
                $ret['person_ids'][] = (int) $p['person_id'];
            }
        }

        return $ret;
    }
}
