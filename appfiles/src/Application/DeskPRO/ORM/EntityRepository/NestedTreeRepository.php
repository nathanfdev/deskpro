<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\ORM\EntityRepository;

use Application\DeskPRO\App;

use Gedmo\Tool\Wrapper\EntityWrapper;
use Doctrine\ORM\Query,
    Gedmo\Tree\Strategy,
    Gedmo\Tree\Strategy\ORM\Nested,
    Gedmo\Exception\InvalidArgumentException,
    Doctrine\ORM\Proxy\Proxy;

use Orb\Util\Arrays;

class NestedTreeRepository extends \Gedmo\Tree\Entity\Repository\NestedTreeRepository
{
	/**
     * Get a simple list of all ID's under a node
     *
     * @param object $node - if null, all tree nodes will be taken
     * @param boolean $direct - true to take only direct children
     */
	public function childrenIds($node = null, $direct = false)
	{
		$meta = $this->getClassMetadata();
		$config = $this->listener->getConfiguration($this->_em, $meta->name);

		$qb = $this->_em->createQueryBuilder();
		$qb->select('PARTIAL node.{id}')->from($meta->rootEntityName, 'node');

		if ($node !== null) {
			if ($node instanceof $meta->rootEntityName) {
				if ($direct) {
					$nodeId = $meta->getSingleIdentifierFieldName();
					$id = $meta->getReflectionProperty($nodeId)->getValue($node);
					$qb->where('node.' . $config['parent'] . ' = ' . $id);
				} else {
					$left = $meta->getReflectionProperty($config['left'])->getValue($node);
					$right = $meta->getReflectionProperty($config['right'])->getValue($node);
					$root = $meta->getReflectionProperty($config['root'])->getValue($node);
					if ($left && $right) {
						$qb->where('node.' . $config['right'] . " < {$right}")
							->andWhere('node.' . $config['left'] . " > {$left}")
							->andWhere('node.' . $config['root'] . " = {$root}");
					}
				}
			} else {
				throw new \InvalidArgumentException("Node is not related to this repository");
			}
		} else {
			if ($direct) {
				$qb->where('node.' . $config['parent'] . ' IS NULL');
			}
		}

		$q = $qb->getQuery();
		$arr =  $q->getResult(Query::HYDRATE_ARRAY);

		$ids = array();
		foreach ($arr as $a) {
			$ids[] = $a['id'];
		}

		return $ids;
	}

	public function reorderAll($sortByField = 'lft', $direction = 'ASC')
    {
		$meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->name);
        $qb = $this->_em->createQueryBuilder();
        $qb->select('node')
            ->from($config['useObjectClass'], 'node')
            ->where('node.' . $config['parent'] . " IS NULL")
            ->orderBy('node.' . $sortByField, 'ASC');
        $q = $qb->getQuery();

		$roots = $q->getResult();
		foreach ($roots as $node) {
			$this->reorder($node, $sortByField, $direction, false);
		}
    }
}
