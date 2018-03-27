<?php

/**
 * DeskPRO.
 *
 * @category Translate
 */

namespace Application\DeskPRO\Translate;

use Orb\Util\Util;

/**
 * This takes an object, and then based on its state, produces a phrase ID that
 * we can use to look up a phrase. This is how we translate thigns like category
 * titles. The category itself becomes a "phrase", and is handled like any other.
 */
class ObjectPhraseNamer
{
    public function getPhraseName($object, $property = null)
    {
        $id = null;
        if (method_exists($object, 'getId')) {
            $id = $object->getId();
        } elseif ($object instanceof \ArrayAccess and isset($object['id'])) {
            $id = $object['id'];
        }

        if ($id) {
            $baseclass = Util::getBaseClassname($object);
            $prefix    = 'obj_'.strtolower($baseclass).'.';
            $name      = $prefix.$id;
            if ($property) {
                $name .= '_'.$property;
            }

            return $name;
        }

        return;
    }

    public function getPhraseDefault($object, $property = null)
    {
        if ($object instanceof \ArrayAccess) {
            if ($property === null) {
                if (isset($object['full_title'])) {
                    return $object['full_title'];
                } elseif (isset($object['title'])) {
                    return $object['title'];
                } elseif (isset($object['name'])) {
                    return $object['title'];
                }
            }

            if (isset($object[$property])) {
                return $object[$property];
            }
        }

        return;
    }
}
