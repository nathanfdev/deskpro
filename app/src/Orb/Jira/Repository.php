<?php

namespace Orb\Jira;

/**
 * Entity Repository
 *
 * @author Abhinav Kumar <work@abhinavkumar.in>
 */
abstract class Repository
{
    /**
     * Associated Entity Class
     *
     * @var String Entity class name
     */
    protected $_entityClass;

    /**
     * Entity REST endpoint
     *
     * @var String the REST endpoint
     */
    protected $_endPoint;

    /**
     * The JIRA Service Client
     *
     * @var \Jira\Service
     */
    protected $_client;

    /**
     * The default constructor
     *
     * @param \Orb\Jira\Service $client The JIRA Service client
     */
    public function __construct(\Orb\Jira\Service $client)
    {
        $this->_client = $client;
    }

    /**
     * Gets the fully qualified entity class name
     *
     * @return String Qualified entity class name
     */
    public function getEntityClass()
    {
        return '\Orb\\Jira\\Entity\\' . $this->_entityClass;
    }

    /**
     * Gets the REST endpoint
     *
     * @return String the REST endpoint
     */
    public function getEndpoint($full = true)
    {
        if ($full) {
            return 'rest/api/latest/' . $this->_endPoint;
        }

        return $this->_endPoint;
    }

    /**
     * Finds an entity
     *
     * @param  String|int                                $id The ID of the entity to find
     * @return boolean|\Orb\Jira\Entity\Repository\class
     */
    public function find($id)
    {
        $client		= $this->_client;

        $endPoint	= $this->getEndpoint();

        if (empty($endPoint)) {
            $client->addError('The class "' . __CLASS__ . '" does not have the endpoint defined . . did you forget it?');

            return false;
        }

        /**
         * We need to check and sanitize the endpoint
         * It must have the trailing slash(/)
         */
        if (substr($endPoint, -1) !== '/') {
            $endPoint .= '/';
        }

        try {
            $response = $client->get($endPoint . $id);
        } catch (\Exception $e) {
            $client->addError($e->getMessage(), $e->getCode());

            return false;
        }

        if (!$response || !count($response)) {
            $client->addError('Resource Not Found: ' . $id);

            return false;
        }

        if (!method_exists($this->getEntityClass(), 'fromArray')) {
            $client->addError('The class "' . __CLASS__ . '" does not have a "fromArray" method defined . . did you forget it?');
        }

        $class	= $this->getEntityClass();

        $entity	= new $class($response);

        if ($entity && is_a($entity, $this->getEntityClass())) {
            return $entity;
        }

        return false;
    }

    /**
     * Persists an entity to the JIRA REST Service
     *
     * @param  \Orb\Jira\Entity\Entity $entity The entity to persist
     * @param  \Orb\Jira\Service       $client The JIRA Service client
     * @return array                   | bool The persisted entity on success and "FALSE" otherwise
     */
    public function persist(\Orb\Jira\Entity $entity, \Orb\Jira\Service $client)
    {
        if (!$entity->getId()) {
            $response = $this->_create($entity, $client);
        } else {
            $response = $this->_update($entity, $client);
        }

        return $response;
    }

    /**
     * Persists a new entity to the JIRA REST Service
     *
     * @param  \Orb\Jira\Entity             $entity The entity to persist
     * @param  \Orb\Jira\Service            $client The JIRA Service client
     * @return \Orb\Jira\Entity\Entity|bool The persisted entity on success and "FALSE" otherwise
     */
    abstract protected function _create(\Orb\Jira\Entity $entity, \Orb\Jira\Service $client);

    /**
     * Persists an existing entity to the JIRA REST Service
     *
     * @param  \Orb\Jira\Entity      $entity The entity to persist
     * @param  \Orb\Jira\Service     $client The JIRA Service client
     * @return \Orb\Jira\Entity|bool The persisted entity on success and "FALSE" otherwise
     */
    abstract protected function _update(\Orb\Jira\Entity $entity, \Orb\Jira\Service $client);

    /**
     * Deletes an entity from the JIRA REST Service
     *
     * @param  \Orb\Jira\Entity  $entity The entity to remove
     * @param  \Orb\Jira\Service $client
     * @return bool              "TRUE" on success and "FALSE" otherwise
     */
    public function remove(\Orb\Jira\Entity\Issue $issue, \Orb\Jira\Service $client)
    {
        return $client->delete($this->getEndpoint() . '/' . $issue->getId());
    }

    /**
     * Gets the repository
     *
     * @param  String                                 $repositoryClass The Repository class name
     * @param  \Orb\Jira\Service                      $client          The JIRA Service client
     * @return \Orb\Jira\Entity\Repository\Repository
     */
    final public static function getRepository($repositoryClass, \Orb\Jira\Service $client)
    {
        return new $repositoryClass($client);
    }
}
