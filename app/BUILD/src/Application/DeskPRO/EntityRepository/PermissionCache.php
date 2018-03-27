<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

class PermissionCache extends AbstractEntityRepository
{
    /**
     * @var array
     */
    private $cache;

    public function loadPermissionTypes($usergroup_key, $person_id = null, array $types = null)
    {
        $usergroup_key = preg_replace('#\-person\-\d+$#', '', $usergroup_key);

        if ($this->cache === null) {
            $this->cache = $this->_em->getConnection()->fetchAll('
                SELECT name, usergroup_key, perms
                FROM permissions_cache
            ');
        }

        if ($person_id) {
            $person_key = $usergroup_key.'-person-'.$person_id;
        } else {
            $person_key = null;
        }

        if ($types) {
            // A simple filter to make sure only valid names are included
            $types = array_filter($types, function ($var) {
                return !preg_match('#[^a-zA-Z0-9_]#', $var);
            });

            if (!$types) {
                return [];
            }

            $types = array_fill_keys($types, true);

            $recs = array_filter($this->cache, function ($c) use ($usergroup_key, $types) {
                return isset($types[$c['name']]) && ($c['usergroup_key'] == $usergroup_key);
            });
            if ($person_key) {
                $recs_override = array_filter($this->cache, function ($c) use ($person_key, $types) {
                    return isset($types[$c['name']]) && $c['usergroup_key'] == $person_key;
                });
                if ($recs_override) {
                    $recs = array_merge($recs, $recs_override);
                }
            }
        } else {
            $recs = array_filter($this->cache, function ($c) use ($usergroup_key) {
                return $c['usergroup_key'] == $usergroup_key;
            });
            if ($person_key) {
                $recs_override = array_filter($this->cache, function ($c) use ($person_key) {
                    return $c['usergroup_key'] == $person_key;
                });
                if ($recs_override) {
                    $recs = array_merge($recs, $recs_override);
                }
            }
        }

        $loaders = [];

        foreach ($recs as &$r) {
            if (isset($r['perms_loader'])) {
                $loaders[] = $r['perms_loader'];
            } elseif (!empty($r['perms'])) {
                $r['perms_loader'] = @unserialize($r['perms']);
                $r['perms']        = null;
                if ($r['perms_loader']) {
                    $r['perms_loader']->loaded_key = $r['usergroup_key'];
                    $loaders[]                     = $r['perms_loader'];
                }
            }
        }

        return $loaders;
    }
}
