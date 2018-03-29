<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Doctrine\ORM\EntityManager;

/**
 * A base service wrapper around a repository. Mostly just to cache query results
 * so things like getting an array of titles dont get executed multiple times, but can be subclassed for more
 * advanced stuff.
 */
class BaseRepositoryService
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    protected $db;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $repos;

    /**
     * @var string
     */
    protected $entity_name = null;

    /**
     * @var array
     */
    protected $call_result = [];

    /**
     * @var \Orb\Util\OptionsArray
     */
    protected $options;

    public static function create(DeskproContainer $container, array $options = null)
    {
        $em = $container->getEm();
        $o  = new static($em, $options);

        return $o;
    }

    /**
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em, array $options = null)
    {
        $this->options = new \Orb\Util\OptionsArray($options);

        if ($this->options->get('entity')) {
            $this->entity_name = $this->options->get('entity');
        }

        $this->em = $em;
        $this->db = $em->getConnection();

        $this->repos = $this->em->getRepository($this->getEntityName());
        $this->init();
    }

    protected function init()
    {
    }

    /**
     * The entity class.
     *
     * @return string
     */
    public function getEntityName()
    {
        return $this->entity_name;
    }

    /**
     * Reset the saved state.
     */
    public function reset()
    {
        $this->call_result = [];
    }

    public function __call($method, array $args = [])
    {
        $hash_seg = [$method];

        if ($args) {
            foreach ($args as $k => $a) {
                if (is_scalar($a)) {
                    $hash_seg[] = $k.':';
                    $hash_seg[] = (string) $a;
                } else {
                    return call_user_func_array([$this->repos, $method], $args);
                }
            }
        }

        $hash = md5(implode('', $hash_seg));
        if (isset($this->call_result[$hash])) {
            return $this->call_result[$hash];
        }

        $this->call_result[$hash] = call_user_func_array([$this->repos, $method], $args);

        return $this->call_result[$hash];
    }
}
