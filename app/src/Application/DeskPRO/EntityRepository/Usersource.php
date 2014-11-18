<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Usersource\UsersourceCollection;
use Application\DeskPRO\Usersource\UsersourceInfo;

class Usersource extends AbstractEntityRepository
{
    /**
     * @var \Application\DeskPRO\Entity\Usersource[]
     */
    protected $usersources = null;

    /**
     * Get all defined usersources
     *
     * @param  bool                                     $active
     * @return \Application\DeskPRO\Entity\Usersource[]
     * @deprecated use the UsersourceManager->getAll() and then filter with the returned UsersourceCollection instead
     */
    public function getAllUsersources($active = true)
    {
        if ($active) {
            if ($this->usersources === null) {
                $this->usersources = $this->getEntityManager()->createQuery("
                    SELECT u
                    FROM DeskPRO:Usersource u INDEX BY u.id
                    WHERE u.is_enabled = true
                    ORDER BY u.display_order ASC, u.title ASC
                ")->execute();
            }

            return $this->usersources;
        } else {
            return $this->getEntityManager()->createQuery("
                SELECT u
                FROM DeskPRO:Usersource u INDEX BY u.id
                ORDER BY u.display_order ASC
            ")->execute();
        }
    }

    /**
     * @param  bool                                     $active
     * @return \Application\DeskPRO\Entity\Usersource[]
     */
    public function getAll()
    {
        return $this->getEntityManager()->createQuery("
            SELECT u
            FROM DeskPRO:Usersource u
        ")->execute();
    }


    /**
     * Fetch all usersources that are capable of logging in using locally-accepted form input.
     * That is, they can handle a username/password combo and can process that in real-time.
     *
     * @return \Application\DeskPRO\Entity\Usersource[]
     * @deprecated use the UsersourceManager->getAll() and then filter with the returned UsersourceCollection instead
     */
    public function getLocalInputUsersources()
    {
        $all = $this->getAllUsersources();

        $ret = array();
        foreach ($all as $us) {
            if ($us->getAdapter()->isCapable(UsersourceInfo::CAPABILITY_FORM_LOGIN)) {
                $ret[$us->id] = $us;
            }
        }

        return $ret;
    }

    /**
     * Fetch all usersources that are capable of logging in using a locally-available cookie.
     * This is generally only suitable for services on the same server (and domain).
     *
     * @return \Application\DeskPRO\Entity\Usersource[]
     * @deprecated use the UsersourceManager->getAll() and then filter with the returned UsersourceCollection instead
     */
    public function getCookieInputUsersources()
    {
        $all = $this->getAllUsersources();

        $ret = array();
        foreach ($all as $us) {
            if ($us->getAdapter()->isCapable(UsersourceInfo::CAPABILITY_COOKIE_LOGIN)) {
                $ret[$us->id] = $us;
            }
        }

        return $ret;
    }

    /**
     * Fetch all usersources that are capable of logging via JS SSO checks.
     *
     * @return \Application\DeskPRO\Entity\Usersource[]
     * @deprecated use the UsersourceManager->getAll() and then filter with the returned UsersourceCollection instead
     */
    public function getJsSsoUsersources()
    {
        $all = $this->getAllUsersources();

        $ret = array();
        foreach ($all as $us) {
            if ($us->getAdapter()->isCapable(UsersourceInfo::CAPABILITY_SSO_JS)) {
                $ret[$us->id] = $us;
            }
        }

        return $ret;
    }

    /**
     * Fetch all usersources that are capable of fetching userinfo without having to
     * authenticate. That is, we can provide a id/username/email and get an array of
     * raw data back.
     *
     * @return \Application\DeskPRO\Entity\Usersource[]
     * @deprecated use the UsersourceManager->getAll() and then filter with the returned UsersourceCollection instead
     */
    public function getUserInfoFetchableUsersources()
    {
        $all = $this->getAllUsersources();

        $ret = array();
        foreach ($all as $us) {
            // TODO: Is this correct?
            if ($us->getAdapter()->isCapable(UsersourceInfo::CAPABILITY_FORM_LOGIN)) {
                $ret[$us->id] = $us;
            }
        }

        return $ret;
    }


    /**
     * Get a usersource of a specific type
     *
     * @param  string                                   $type
     * @return \Application\DeskPRO\Entity\Usersource[]
     */
    public function getByType($type, $multiple = false)
    {
        $dql = "SELECT u FROM DeskPRO:Usersource u WHERE u.source_type = ?1";

        if ($multiple) {
            return $this->getEntityManager()->createQuery($dql)->setParameter(1, $type)->execute();
        } else {
            return $this->getEntityManager()->createQuery($dql)->setParameter(1, $type)->setMaxResults(1)->getOneOrNullResult();
        }
    }



    /**
     * Get a usersource by its ID
     *
     * @param  int                                    $id
     * @return \Application\DeskPRO\Entity\Usersource
     */
    public function getUsersource($id)
    {
        if ($this->usersources === null) $this->getAllUsersources();
        return $this->usersources[$id];
    }


    /**
     * Get an array of all usersource IDs
     *
     * @return int[]
     */
    public function getUsersourceIds()
    {
        if ($this->usersources === null) $this->getAllUsersources();
        return array_keys($this->usersources);
    }


    public function updateDisplayOrders(array $display_orders)
    {
        $display_orders = array_values($display_orders);

        $db = $this->_em->getConnection();
        $db->beginTransaction();

        $x = 0;
        foreach ($display_orders as $tr_id) {
            $x += 10;
            $db->update($this->getTableName(), array('display_order' => $x), array('id' => $tr_id));
        }

        $db->commit();
    }

}
