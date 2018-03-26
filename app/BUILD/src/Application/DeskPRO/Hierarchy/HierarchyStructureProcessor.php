<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Hierarchy;

use Doctrine\ORM\EntityManager;

/**
 * This helps take an array of arrays that describe a tree, and
 * persist it to the database.
 */
class HierarchyStructureProcessor
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var string
     */
    private $entity_name;

    /**
     * @var array
     */
    private $properties;

    /**
     * False to disable any hierarchy handling.
     *
     * @var bool
     */
    private $is_hierarchy = true;

    /**
     * @param EntityManager $em
     * @param string        $entity_name The name of the entity to save to
     * @param string[]      $properties  Array of property names to assign from the array (id, display_order and title are always used/detected)
     */
    public function __construct(EntityManager $em, $entity_name, array $properties = [])
    {
        $this->em          = $em;
        $this->entity_name = $entity_name;
        $this->properties  = $properties;
    }

    /**
     * Disables hierarchy handling.
     */
    public function disableHierarchy()
    {
        $this->is_hierarchy = false;
    }

    /**
     * Returns an array of entities that are ready to be saved or validated.
     *
     * @param array $structure
     *
     * @return array
     */
    public function getRecords(array $structure)
    {
        $recs                = [];
        $child_to_parent_map = [];

        //------------------------------
        // First pass is to gather info:
        // - Loads cats
        // - Creates pristine entities for new ones
        // - Maps children to their parents
        //------------------------------

        $cat_ids   = [];
        $classname = $this->em->getRepository($this->entity_name)->getClassName();
        foreach ($structure as $cat) {
            if (empty($cat['@is_new'])) {
                $cat_ids[] = $cat['id'];
            } else {
                $obj              = new $classname();
                $recs[$cat['id']] = $obj;
            }

            if ($this->is_hierarchy && !empty($cat['parent_id']) && $cat['parent_id']) {
                $child_to_parent_map[$cat['id']] = $cat['parent_id'];
            }
        }

        if ($cat_ids) {
            foreach ($this->em->getRepository($this->entity_name)->getByIds($cat_ids) as $k => $v) {
                $recs[$k] = $v;
            }
        }

        //------------------------------
        // Second pass applies the array to the object
        //------------------------------

        foreach ($structure as $cat) {
            $cat_id = $cat['id'];
            if (!isset($recs[$cat_id])) {
                throw new \Exception('Could not find category in collection: '.$cat_id);
            }

            $obj = $recs[$cat_id];
            $this->applyProperties($obj, $cat);

            // Also hook up children to their parents
            if ($this->is_hierarchy) {
                if (isset($child_to_parent_map[$cat_id])) {
                    $parent_obj  = $recs[$child_to_parent_map[$cat_id]];
                    $obj->parent = $parent_obj;
                } else {
                    $obj->parent = null;
                }
            }
        }

        return $recs;
    }

    /**
     * @param array $records
     * @param bool  $remove_missing True to delete records that are not in the record array (e.g., they were deleted from the structure)
     *
     * @return array
     */
    public function saveRecords(array $records, $remove_missing = false)
    {
        if ($remove_missing) {
            $have_ids = [0];
            foreach ($records as $r) {
                if ($r->id) {
                    $have_ids[] = $r->id;
                }
            }

            $missing_recs = $this->em->createQuery("
				SELECT r
				FROM {$this->entity_name} r
				WHERE r NOT IN (?0)
			")->execute([$have_ids]);

            foreach ($missing_recs as $r) {
                if ($this->is_hierarchy && $r->parent) {
                    $this->em->remove($r);
                }
            }
            foreach ($missing_recs as $r) {
                $this->em->remove($r);
            }
        }

        foreach ($records as $r) {
            $this->em->persist($r);
        }

        $this->em->getConnection()->beginTransaction();
        try {
            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }

        return $records;
    }

    /**
     * This is the same as calling getRecords followed by saveRecords.
     *
     * @param array $structure
     *
     * @return array
     */
    public function save(array $structure)
    {
        $recs = $this->getRecords($structure);
        $this->saveRecords($recs, true);

        return $recs;
    }

    /**
     * Apply properties to the category object.
     *
     * @param mixed $object
     * @param array $values
     */
    private function applyProperties($object, array $values)
    {
        foreach ($this->properties as $k) {
            if (array_key_exists($k, $values)) {
                $object->$k = $values[$k];
            }
        }

        // Built-in props
        foreach (['title', 'display_order'] as $k) {
            if (array_key_exists($k, $values)) {
                $object->$k = $values[$k];
            }
        }
    }
}
