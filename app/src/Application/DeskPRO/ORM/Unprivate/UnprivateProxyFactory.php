<?php
/* This file has been auto-generated (2014-10-06). See build-vendors-mutate.php */
namespace Application\DeskPRO\ORM\Unprivate;
use Doctrine\ORM\Proxy\ProxyException;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\Common\Proxy\ProxyDefinition;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Proxy\Proxy as BaseProxy;
use Application\DeskPRO\ORM\Proxy\ProxyGenerator;
use Doctrine\ORM\ORMInvalidArgumentException;
use Doctrine\ORM\Persisters\BasicEntityPersister;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
class UnprivateProxyFactory extends AbstractProxyFactory
{
    protected $em;
    protected $uow;
    protected $proxyNs;
    public function __construct(EntityManager $em, $proxyDir, $proxyNs, $autoGenerate = false)
    {
        $proxyGenerator = new ProxyGenerator($proxyDir, $proxyNs);
        $proxyGenerator->setPlaceholder('baseProxyInterface', 'Doctrine\ORM\Proxy\Proxy');
        parent::__construct($proxyGenerator, $em->getMetadataFactory(), $autoGenerate);
        $this->em      = $em;
        $this->uow     = $em->getUnitOfWork();
        $this->proxyNs = $proxyNs;
    }
    protected function skipClass(ClassMetadata $metadata)
    {
        return $metadata->isMappedSuperclass || $metadata->getReflectionClass()->isAbstract();
    }
    protected function createProxyDefinition($className)
    {
        $classMetadata   = $this->em->getClassMetadata($className);
        $entityPersister = $this->uow->getEntityPersister($className);
        return new ProxyDefinition(
            ClassUtils::generateProxyClassName($className, $this->proxyNs),
            $classMetadata->getIdentifierFieldNames(),
            $classMetadata->getReflectionProperties(),
            $this->createInitializer($classMetadata, $entityPersister),
            $this->createCloner($classMetadata, $entityPersister)
        );
    }
    protected function createInitializer(ClassMetadata $classMetadata, BasicEntityPersister $entityPersister)
    {
        if ($classMetadata->getReflectionClass()->hasMethod('__wakeup')) {
            return function (BaseProxy $proxy) use ($entityPersister, $classMetadata) {
                $initializer = $proxy->__getInitializer();
                $cloner      = $proxy->__getCloner();
                $proxy->__setInitializer(null);
                $proxy->__setCloner(null);
                if ($proxy->__isInitialized()) {
                    return;
                }
                $properties = $proxy->__getLazyProperties();
                foreach ($properties as $propertyName => $property) {
                    if (!isset($proxy->$propertyName)) {
                        $proxy->$propertyName = $properties[$propertyName];
                    }
                }
                $proxy->__setInitialized(true);
                $proxy->__wakeup();
                if (null === $entityPersister->load($classMetadata->getIdentifierValues($proxy), $proxy)) {
                    $proxy->__setInitializer($initializer);
                    $proxy->__setCloner($cloner);
                    $proxy->__setInitialized(false);
                    throw new EntityNotFoundException();
                }
            };
        }
        return function (BaseProxy $proxy) use ($entityPersister, $classMetadata) {
            $initializer = $proxy->__getInitializer();
            $cloner      = $proxy->__getCloner();
            $proxy->__setInitializer(null);
            $proxy->__setCloner(null);
            if ($proxy->__isInitialized()) {
                return;
            }
            $properties = $proxy->__getLazyProperties();
            foreach ($properties as $propertyName => $property) {
                if (!isset($proxy->$propertyName)) {
                    $proxy->$propertyName = $properties[$propertyName];
                }
            }
            $proxy->__setInitialized(true);
            if (null === $entityPersister->load($classMetadata->getIdentifierValues($proxy), $proxy)) {
                $proxy->__setInitializer($initializer);
                $proxy->__setCloner($cloner);
                $proxy->__setInitialized(false);
                throw new EntityNotFoundException();
            }
        };
    }
    protected function createCloner(ClassMetadata $classMetadata, BasicEntityPersister $entityPersister)
    {
        return function (BaseProxy $proxy) use ($entityPersister, $classMetadata) {
            if ($proxy->__isInitialized()) {
                return;
            }
            $proxy->__setInitialized(true);
            $proxy->__setInitializer(null);
            $class = $entityPersister->getClassMetadata();
            $original = $entityPersister->load($classMetadata->getIdentifierValues($proxy));
            if (null === $original) {
                throw new EntityNotFoundException();
            }
            foreach ($class->getReflectionClass()->getProperties() as $reflectionProperty) {
                $propertyName = $reflectionProperty->getName();
                if ($class->hasField($propertyName) || $class->hasAssociation($propertyName)) {
                    $reflectionProperty->setAccessible(true);
                    $reflectionProperty->setValue($proxy, $reflectionProperty->getValue($original));
                }
            }
        };
    }
}
