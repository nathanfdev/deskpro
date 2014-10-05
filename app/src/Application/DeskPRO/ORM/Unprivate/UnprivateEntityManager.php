<?php
/* This file has been auto-generated (2014-09-04). See build-vendors-mutate.php */
namespace Application\DeskPRO\ORM\Unprivate;
use Doctrine\ORM\Configuration, Doctrine\ORM\ORMException, Doctrine\ORM\UnitOfWork, Doctrine\ORM\Query, Doctrine\ORM\Internal, Doctrine\ORM\NativeQuery, Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\ORMInvalidArgumentException;
use Exception;
use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Proxy\ProxyFactory;
use Doctrine\ORM\Query\FilterCollection;
use Doctrine\Common\Util\ClassUtils;
class UnprivateEntityManager extends \Doctrine\ORM\EntityManager
{
    protected $config;
    protected $conn;
    protected $metadataFactory;
    protected $unitOfWork;
    protected $eventManager;
    protected $proxyFactory;
    protected $repositoryFactory;
    protected $expressionBuilder;
    protected $closed = false;
    protected $filterCollection;
    protected function __construct(Connection $conn, Configuration $config, EventManager $eventManager)
    {
        $this->conn              = $conn;
        $this->config            = $config;
        $this->eventManager      = $eventManager;
        $metadataFactoryClassName = $config->getClassMetadataFactoryName();
        $this->metadataFactory = new $metadataFactoryClassName;
        $this->metadataFactory->setEntityManager($this);
        $this->metadataFactory->setCacheDriver($this->config->getMetadataCacheImpl());
        $this->repositoryFactory = $config->getRepositoryFactory();
        $this->unitOfWork        = new UnitOfWork($this);
        $this->proxyFactory      = new ProxyFactory(
            $this,
            $config->getProxyDir(),
            $config->getProxyNamespace(),
            $config->getAutoGenerateProxyClasses()
        );
    }
    public function getConnection()
    {
        return $this->conn;
    }
    public function getMetadataFactory()
    {
        return $this->metadataFactory;
    }
    public function getExpressionBuilder()
    {
        if ($this->expressionBuilder === null) {
            $this->expressionBuilder = new Query\Expr;
        }
        return $this->expressionBuilder;
    }
    public function beginTransaction()
    {
        $this->conn->beginTransaction();
    }
    public function transactional($func)
    {
        if (!is_callable($func)) {
            throw new \InvalidArgumentException('Expected argument of type "callable", got "' . gettype($func) . '"');
        }
        $this->conn->beginTransaction();
        try {
            $return = call_user_func($func, $this);
            $this->flush();
            $this->conn->commit();
            return $return ?: true;
        } catch (Exception $e) {
            $this->close();
            $this->conn->rollback();
            throw $e;
        }
    }
    public function commit()
    {
        $this->conn->commit();
    }
    public function rollback()
    {
        $this->conn->rollback();
    }
    public function getClassMetadata($className)
    {
        return $this->metadataFactory->getMetadataFor($className);
    }
    public function createQuery($dql = '')
    {
        $query = new Query($this);
        if ( ! empty($dql)) {
            $query->setDql($dql);
        }
        return $query;
    }
    public function createNamedQuery($name)
    {
        return $this->createQuery($this->config->getNamedQuery($name));
    }
    public function createNativeQuery($sql, ResultSetMapping $rsm)
    {
        $query = new NativeQuery($this);
        $query->setSql($sql);
        $query->setResultSetMapping($rsm);
        return $query;
    }
    public function createNamedNativeQuery($name)
    {
        list($sql, $rsm) = $this->config->getNamedNativeQuery($name);
        return $this->createNativeQuery($sql, $rsm);
    }
    public function createQueryBuilder()
    {
        return new QueryBuilder($this);
    }
    public function flush($entity = null)
    {
        $this->errorIfClosed();
        $this->unitOfWork->commit($entity);
    }
    public function find($entityName, $id, $lockMode = LockMode::NONE, $lockVersion = null)
    {
        $class = $this->metadataFactory->getMetadataFor(ltrim($entityName, '\\'));
        if (is_object($id) && $this->metadataFactory->hasMetadataFor(ClassUtils::getClass($id))) {
            $id = $this->unitOfWork->getSingleIdentifierValue($id);
            if ($id === null) {
                throw ORMInvalidArgumentException::invalidIdentifierBindingEntity();
            }
        }
        if ( ! is_array($id)) {
            $id = array($class->identifier[0] => $id);
        }
        $sortedId = array();
        foreach ($class->identifier as $identifier) {
            if ( ! isset($id[$identifier])) {
                throw ORMException::missingIdentifierField($class->name, $identifier);
            }
            $sortedId[$identifier] = $id[$identifier];
        }
        $unitOfWork = $this->getUnitOfWork();
                if (($entity = $unitOfWork->tryGetById($sortedId, $class->rootEntityName)) !== false) {
            if ( ! ($entity instanceof $class->name)) {
                return null;
            }
            switch ($lockMode) {
                case LockMode::OPTIMISTIC:
                    $this->lock($entity, $lockMode, $lockVersion);
                    break;
                case LockMode::PESSIMISTIC_READ:
                case LockMode::PESSIMISTIC_WRITE:
                    $persister = $unitOfWork->getEntityPersister($class->name);
                    $persister->refresh($sortedId, $entity, $lockMode);
                    break;
            }
            return $entity;         }
        $persister = $unitOfWork->getEntityPersister($class->name);
        switch ($lockMode) {
            case LockMode::NONE:
                return $persister->load($sortedId);
            case LockMode::OPTIMISTIC:
                if ( ! $class->isVersioned) {
                    throw OptimisticLockException::notVersioned($class->name);
                }
                $entity = $persister->load($sortedId);
                $unitOfWork->lock($entity, $lockMode, $lockVersion);
                return $entity;
            default:
                if ( ! $this->getConnection()->isTransactionActive()) {
                    throw TransactionRequiredException::transactionRequired();
                }
                return $persister->load($sortedId, null, null, array(), $lockMode);
        }
    }
    public function getReference($entityName, $id)
    {
        $class = $this->metadataFactory->getMetadataFor(ltrim($entityName, '\\'));
        if ( ! is_array($id)) {
            $id = array($class->identifier[0] => $id);
        }
        $sortedId = array();
        foreach ($class->identifier as $identifier) {
            if ( ! isset($id[$identifier])) {
                throw ORMException::missingIdentifierField($class->name, $identifier);
            }
            $sortedId[$identifier] = $id[$identifier];
        }
                if (($entity = $this->unitOfWork->tryGetById($sortedId, $class->rootEntityName)) !== false) {
            return ($entity instanceof $class->name) ? $entity : null;
        }
        if ($class->subClasses) {
            return $this->find($entityName, $sortedId);
        }
        if ( ! is_array($sortedId)) {
            $sortedId = array($class->identifier[0] => $sortedId);
        }
        $entity = $this->proxyFactory->getProxy($class->name, $sortedId);
        $this->unitOfWork->registerManaged($entity, $sortedId, array());
        return $entity;
    }
    public function getPartialReference($entityName, $identifier)
    {
        $class = $this->metadataFactory->getMetadataFor(ltrim($entityName, '\\'));
                if (($entity = $this->unitOfWork->tryGetById($identifier, $class->rootEntityName)) !== false) {
            return ($entity instanceof $class->name) ? $entity : null;
        }
        if ( ! is_array($identifier)) {
            $identifier = array($class->identifier[0] => $identifier);
        }
        $entity = $class->newInstance();
        $class->setIdentifierValues($entity, $identifier);
        $this->unitOfWork->registerManaged($entity, $identifier, array());
        $this->unitOfWork->markReadOnly($entity);
        return $entity;
    }
    public function clear($entityName = null)
    {
        $this->unitOfWork->clear($entityName);
    }
    public function close()
    {
        $this->clear();
        $this->closed = true;
    }
    public function persist($entity)
    {
        if ( ! is_object($entity)) {
            throw ORMInvalidArgumentException::invalidObject('EntityManager#persist()' , $entity);
        }
        $this->errorIfClosed();
        $this->unitOfWork->persist($entity);
    }
    public function remove($entity)
    {
        if ( ! is_object($entity)) {
            throw ORMInvalidArgumentException::invalidObject('EntityManager#remove()' , $entity);
        }
        $this->errorIfClosed();
        $this->unitOfWork->remove($entity);
    }
    public function refresh($entity)
    {
        if ( ! is_object($entity)) {
            throw ORMInvalidArgumentException::invalidObject('EntityManager#refresh()' , $entity);
        }
        $this->errorIfClosed();
        $this->unitOfWork->refresh($entity);
    }
    public function detach($entity)
    {
        if ( ! is_object($entity)) {
            throw ORMInvalidArgumentException::invalidObject('EntityManager#detach()' , $entity);
        }
        $this->unitOfWork->detach($entity);
    }
    public function merge($entity)
    {
        if ( ! is_object($entity)) {
            throw ORMInvalidArgumentException::invalidObject('EntityManager#merge()' , $entity);
        }
        $this->errorIfClosed();
        return $this->unitOfWork->merge($entity);
    }
    public function copy($entity, $deep = false)
    {
        throw new \BadMethodCallException("Not implemented.");
    }
    public function lock($entity, $lockMode, $lockVersion = null)
    {
        $this->unitOfWork->lock($entity, $lockMode, $lockVersion);
    }
    public function getRepository($entityName)
    {
        return $this->repositoryFactory->getRepository($this, $entityName);
    }
    public function contains($entity)
    {
        return $this->unitOfWork->isScheduledForInsert($entity)
            || $this->unitOfWork->isInIdentityMap($entity)
            && ! $this->unitOfWork->isScheduledForDelete($entity);
    }
    public function getEventManager()
    {
        return $this->eventManager;
    }
    public function getConfiguration()
    {
        return $this->config;
    }
    protected function errorIfClosed()
    {
        if ($this->closed) {
            throw ORMException::entityManagerClosed();
        }
    }
    public function isOpen()
    {
        return (!$this->closed);
    }
    public function getUnitOfWork()
    {
        return $this->unitOfWork;
    }
    public function getHydrator($hydrationMode)
    {
        return $this->newHydrator($hydrationMode);
    }
    public function newHydrator($hydrationMode)
    {
        switch ($hydrationMode) {
            case Query::HYDRATE_OBJECT:
                return new Internal\Hydration\ObjectHydrator($this);
            case Query::HYDRATE_ARRAY:
                return new Internal\Hydration\ArrayHydrator($this);
            case Query::HYDRATE_SCALAR:
                return new Internal\Hydration\ScalarHydrator($this);
            case Query::HYDRATE_SINGLE_SCALAR:
                return new Internal\Hydration\SingleScalarHydrator($this);
            case Query::HYDRATE_SIMPLEOBJECT:
                return new Internal\Hydration\SimpleObjectHydrator($this);
            default:
                if (($class = $this->config->getCustomHydrationMode($hydrationMode)) !== null) {
                    return new $class($this);
                }
        }
        throw ORMException::invalidHydrationMode($hydrationMode);
    }
    public function getProxyFactory()
    {
        return $this->proxyFactory;
    }
    public function initializeObject($obj)
    {
        $this->unitOfWork->initializeObject($obj);
    }
    public static function create($conn, Configuration $config, EventManager $eventManager = null)
    {
        if ( ! $config->getMetadataDriverImpl()) {
            throw ORMException::missingMappingDriverImpl();
        }
        switch (true) {
            case (is_array($conn)):
                $conn = \Doctrine\DBAL\DriverManager::getConnection(
                    $conn, $config, ($eventManager ?: new EventManager())
                );
                break;
            case ($conn instanceof Connection):
                if ($eventManager !== null && $conn->getEventManager() !== $eventManager) {
                     throw ORMException::mismatchedEventManager();
                }
                break;
            default:
                throw new \InvalidArgumentException("Invalid argument: " . $conn);
        }
        return new static($conn, $config, $conn->getEventManager());
    }
    public function getFilters()
    {
        if (null === $this->filterCollection) {
            $this->filterCollection = new FilterCollection($this);
        }
        return $this->filterCollection;
    }
    public function isFiltersStateClean()
    {
        return null === $this->filterCollection || $this->filterCollection->isClean();
    }
    public function hasFilters()
    {
        return null !== $this->filterCollection;
    }
}
