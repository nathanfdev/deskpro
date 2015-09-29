<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\DeskPRO\ORM\Unprivate;

use Application\DeskPRO\ORM\Proxy\ProxyGenerator;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\Common\Proxy\Proxy as BaseProxy;
use Doctrine\Common\Proxy\ProxyDefinition;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Persisters\BasicEntityPersister;

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
            $class    = $entityPersister->getClassMetadata();
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
