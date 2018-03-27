<?php

/**
 * DeskPRO.
 */

namespace Orb\Auth\Adapter;

/**
 * Auth adapters that implement this usersource can return raw userinfo given an ID.
 * For example, DbTable can return a database row which has nothing to do with authenticating.
 */
interface UserInfoFetchableInterface
{
    /**
     * Fetch userinfo based on $id. $id must be something unique, but the field itself
     * is unknown. It might be a userid, a username or an email address, or something else.
     *
     * $id_type is used to specify the specific type $id is. If it is null, then the implementation
     * must guest or simply return null for no-match.
     *
     * Standard strings for $id_type are: id, username, email.
     *
     * This method must return null if no match was found.
     *
     * @param mixed $id
     * @param mixed $id_type
     *
     * @return mixed
     */
    public function getUserInfoFromIdentity($id, $id_type = null);
}
