<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Tree\TreeProxyHasPhraseName;
use Orb\Util\Arrays;

class DepartmentDataService extends BaseRepositoryService
{
    /** @var bool */
    protected $has_init = false;
    /** @var array */
    protected $cats;
    /** @var array */
    protected $cat_ids = [];
    /** @var array */
    protected $root_node_ids = [];
    /** @var array */
    protected $leaf_node_ids = [];
    /** @var array */
    protected $nodes_with_children = [];
    /** @var array */
    protected $filtered_nodes = [];
    /** @var array */
    protected $filtered_chat_nodes = [];

    /**
     * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    protected $continer;

    /**
     * @var \Application\DeskPRO\Translate\Translate
     */
    protected $translator;

    /**
     * @var int
     */
    protected $default_id;

    public static function create(DeskproContainer $container, array $options = null)
    {
        if (!$options) {
            $options = [];
        }
        $options['entity']     = Department::class;
        $options['translator'] = $container->getTranslator();
        $options['default_id'] = $container->getSetting('core.default_ticket_dep');
        $options['container']  = $container;

        $em = $container->getEm();
        $o  = new static($em, $options);

        return $o;
    }

    protected function init()
    {
        $this->translator = $this->options['translator'];
        $this->default_id = $this->options['default_id'];
        $this->continer   = $this->options['container'];
    }

    /**
     * @param $dep_id
     *
     * @return Department
     */
    public function get($dep_id)
    {
        $this->preload();

        return isset($this->cats[$dep_id]) ? $this->cats[$dep_id] : null;
    }

    public function getAll()
    {
        $this->preload();

        return $this->cats;
    }

    protected function preload()
    {
        if ($this->has_init) {
            return;
        }
        $this->has_init = true;

        $this->cats = $this->em->createQuery('
            SELECT d, ch
            FROM DeskPRO:Department d INDEX BY d.id
            LEFT JOIN d.children ch
            ORDER BY d.display_order ASC
        ')->execute();

        $cats = [];

        // force hydration
        foreach ($this->cats as $c) {
            $this->cat_ids[] = $c->getId();
            $c->getTitle();
            $c->__dp_is_preloaded_repos = $this;

            $cats[$c->getId()] = [
                'id'        => $c->getId(),
                'parent_id' => $c->parent ? $c->parent->getId() : 0,
                'title'     => $c->getTitle(),
            ];

            if (!$c->parent) {
                $this->root_node_ids[] = $c->getId();
            }
        }
        foreach ($this->cats as $c) {
            foreach ($c->children as $sc) {
                $this->nodes_with_children[$c->getId()] = true;
                $this->leaf_node_ids[]                  = $sc->getId();
            }
        }

        $this->repos->getInHierarchy($cats);
    }

    public function getNames($for_ids = null)
    {
        $this->preload();

        $ret = [];

        if ($for_ids) {
            foreach ($for_ids as $cid) {
                if (!$this->get($cid)) {
                    $ret[$cid] = "Unknown #$cid";
                } else {
                    $ret[$cid] = $this->translator->getPhraseObject($this->get($cid), 'title');
                }
            }
        } else {
            foreach ($this->cat_ids as $cid) {
                $ret[$cid] = $this->translator->getPhraseObject($this->get($cid), 'title');
            }
        }

        return $ret;
    }

    public function getByIds(array $ids, $keep_order = false)
    {
        $this->preload();
        $ret = [];

        foreach ($ids as $id) {
            if (isset($this->cats[$id])) {
                $ret[$id] = $this->cats[$id];
            }
        }

        return $ret;
    }

    public function getChildren($category = null, $direct = true)
    {
        $this->preload();
        $ids = $this->repos->getChildrenIds($category, $direct);
        if (!$ids) {
            return [];
        }

        return $this->getByIds($ids);
    }

    /**
     * @param Person     $personContext
     * @param            $app
     * @param array      $allowIds
     * @param string     $permission
     * @param Brand|null $brand         Limit only departments to this brand if present
     *
     * @return array|mixed
     */
    public function getPersonDepartments(
        Person $personContext,
        $app,
        array $allowIds = [],
        $permission = 'full',
        $brand = null
    ) {
        $key = md5($personContext->getId().'.'.$app);

        if (isset($this->filtered_nodes[$key])) {
            return $this->filtered_nodes[$key];
        }

        if ($allowIds) {
            foreach (array_values($allowIds) as $id) {
                $d = $this->get($id);
                if ($d && $d->parent) {
                    $allowIds[] = $d->parent->getId();
                }
            }

            $allowIds = array_unique($allowIds);
            $allowIds = array_combine(array_values($allowIds), array_values($allowIds));
        }

        $filter = function ($c) use ($personContext, $app, $allowIds, $permission, $brand) {
            /* @var Department $c */
            if ($brand) {
                if (count($c->getChildren()) === 0 && !$c->hasBrand($brand)) {
                    return false;
                } elseif (count($c->getChildren()) > 0) {
                    $allowedChild = false;
                    foreach ($c->getChildren() as $child) {
                        if ($child->hasBrand($brand) &&
                            (isset($allowIds[$child->getId()]) || $personContext->getPermissionsManager()->Departments->isAllowed($child->getId(), $app, $permission))) {
                            $allowedChild = true;
                            break;
                        }
                    }
                    if (!$allowedChild) {
                        return false;
                    }
                }
            }
            if (isset($allowIds[$c->getId()])) {
                return true;
            }

            return $personContext->getPermissionsManager()->Departments->isAllowed($c->getId(), $app, $permission);
        };

        if (!$allowIds) {
            $this->filtered_nodes[$key] = TreeProxyHasPhraseName::makeTreeProxyArray($this->getRootNodes(), $filter);

            return $this->filtered_nodes[$key];
        } else {
            $nodes = TreeProxyHasPhraseName::makeTreeProxyArray($this->getRootNodes(), $filter);

            return $nodes;
        }
    }

    public function getOnlineChatDepartments(Person $person_context, array $only_ids = null)
    {
        $key = $person_context->getId();

        if (isset($this->filtered_chat_nodes[$key]) && !$only_ids) {
            return $this->filtered_chat_nodes[$key];
        }

        $online_dep_ids = [];

        $agents_online_ids = $this->em->getRepository('DeskPRO:Session')->getAvailableAgentIds();
        foreach ($agents_online_ids as $aid) {
            $agent = $this->continer->getDataService('Agent')->get($aid);
            if (!$agent) {
                continue;
            }

            $agent->loadHelper('AgentPermissions');

            $online_dep_ids = array_merge(
                $online_dep_ids,
                $agent->getHelper('AgentPermissions')->getAllowedDepartments('chat')
            );
        }

        $online_dep_ids = array_unique($online_dep_ids, \SORT_NUMERIC);
        $online_dep_ids = array_values($online_dep_ids);
        $online_dep_ids = Arrays::castToType($online_dep_ids, 'int');

        // We only want these specific IDs
        if ($only_ids) {
            $only_ids       = Arrays::castToType($only_ids, 'int');
            $online_dep_ids = array_intersect($online_dep_ids, $only_ids);
        }

        if ($online_dep_ids) {
            $online_dep_ids = array_combine($online_dep_ids, $online_dep_ids);
        }

        if (!$online_dep_ids) {
            if (!$only_ids) {
                $this->filtered_nodes[$key] = [];
            }

            return [];
        }

        $filter = function ($c) use ($person_context, $online_dep_ids) {
            if (!isset($online_dep_ids[$c->getId()])) {
                return false;
            }

            return $person_context->getPermissionsManager()->Departments->isAllowed($c->getId(), 'chat', 'full');
        };

        $proxy = TreeProxyHasPhraseName::makeTreeProxyArray($this->getRootNodes(), $filter);

        if (!$only_ids) {
            $this->filtered_nodes[$key] = $proxy;
        }

        return $proxy;
    }

    public function getRootNodes()
    {
        $this->preload();

        if (!$this->root_node_ids) {
            return [];
        }

        return $this->getByIds($this->root_node_ids);
    }

    public function getParentNodes()
    {
        $this->preload();

        return $this->getByIds(array_keys($this->nodes_with_children));
    }

    public function getParentNodeIds()
    {
        $this->preload();

        return array_keys($this->nodes_with_children);
    }

    public function getLeafNodeIds()
    {
        $this->preload();

        return $this->leaf_node_ids;
    }

    public function getLeafNodes()
    {
        $this->preload();

        return $this->getByIds($this->leaf_node_ids);
    }

    public function getPath($category)
    {
        $this->preload();
        $ids = $this->repos->getPathIds($category);

        if (!$ids) {
            return [];
        }

        return $this->getByIds($ids);
    }

    public function getDefaultTicketDepartment()
    {
        $this->preload();

        if (!$this->default_id || !isset($this->cats[$this->default_id]) || count($this->getChildren($this->default_id)) || !$this->cats[$this->default_id]->is_tickets_enabled) {
            $this->default_id = 0;
        }

        // Invalid default just chooses first
        if (!$this->default_id) {
            foreach ($this->cats as $c) {
                if ($c->is_tickets_enabled && !count($this->getChildren($c))) {
                    $this->default_id = $c->getId();
                    break;
                }
            }
        }

        return $this->cats[$this->default_id];
    }

    public function getFullNames($type = 'tickets', $include_parents = true)
    {
        $names = [];
        foreach ($this->getRootNodes() as $dep) {
            if (!$dep->isType($type)) {
                continue;
            }

            $children = $this->getChildren($dep);

            if ($include_parents || !$children) {
                $names[$dep->getId()] = $dep->title;
            }

            foreach ($children as $subdep) {
                if (!$subdep->isType($type)) {
                    continue;
                }

                $names[$subdep->getId()] = $dep->title.' > '.$subdep->title;
            }
        }

        return $names;
    }

    public function __call($method, array $args = [])
    {
        $this->preload();

        return parent::__call($method, $args);
    }
}
