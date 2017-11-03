<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppStorage;

use DeskPRO\Bundle\AppBundle\Entity;
use DeskPRO\Bundle\AppStoreBundle\Domain;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure;
use Doctrine\Orm\EntityManager;

class AccessService
{
    /** @var EntityManager */
    private $entityManager;

    /** @var Infrastructure\EntityQueryBuilders */
    private $queryBuilder;

    /**
     * @param EntityManager                           $entityManager
     * @param Infrastructure\EntityQueryBuilders|null $qb
     */
    public function __construct(EntityManager $entityManager, Infrastructure\EntityQueryBuilders $qb = null)
    {
        $this->entityManager = $entityManager;

        if (is_null($qb)) {
            $this->queryBuilder = new Infrastructure\EntityQueryBuilders();
        } else {
            $this->queryBuilder = $qb;
        }
    }

    /**
     * @param AccessRequest $request
     * @param Domain\AppStorageItem $item
     * @return bool
     */
    public function allowWriteAccess(AccessRequest $request, Domain\AppStorageItem $item)
    {
        $allowsPersonAccess =
            $request->getAccessLevel() === Domain\Constants::ACCESS_LEVEL_WRITE
            && $item->confirmAccessLevelForPerson($request->getAuthPersonId(), Domain\Constants::ACCESS_LEVEL_WRITE)
        ;

        return $allowsPersonAccess;
    }

    /**
     * @param AccessRequest $request
     * @param Domain\AppStorageItem $item
     * @return bool
     */
    public function allowReadAccess(AccessRequest $request, Domain\AppStorageItem $item)
    {
        $allowsPersonAccess = $request->getAccessLevel() === Domain\Constants::ACCESS_LEVEL_READ
            && $item->confirmAccessLevelForPerson($request->getAuthPersonId(), Domain\Constants::ACCESS_LEVEL_READ)
        ;

        if (!$allowsPersonAccess) {
            return false;
        }

        if ($request instanceof ServiceAccessRequest) {
            return $item->confirmAccessLevelForService($request->getService(), Domain\Constants::ACCESS_LEVEL_READ);
        }

        return true;
    }

    /**
     * @param Domain\AppStorageItemIdentifier $identifier
     * @param AccessRequest             $request
     *
     * @return bool
     */
    public function allowsAccess(AccessRequest $request, Domain\AppStorageItemIdentifier $identifier)
    {
        /** @var Entity\AppStore\AppState $stateEntity */
        $stateEntity          = null;
        $findStateEntityQuery = $this->queryBuilder->buildFindStateEntityByIdQuery($this->entityManager, $identifier);
        $result               = $findStateEntityQuery->getResult();
        if (1 === count($result)) { //instance not found or more than one
            $stateEntity = array_pop($result);
        }

        if (is_null($stateEntity)) {
            return false;
        }

        $converter = new StateEntityConverter();
        $state     = $converter->toDomainObject($stateEntity);

        return $this->allowReadAccess($request, $state) || $this->allowWriteAccess($request, $state);
    }

    /**
     * @param Domain\AppStorageItemIdentifier $identifier
     *
     * @return Domain\AppStorage\AccessOptions|null
     */
    public function findAccessOptions(Domain\AppStorageItemIdentifier $identifier)
    {
        $finder      = new Infrastructure\ApplicationDoctrineFinder($this->entityManager, $this->queryBuilder);
        $application = $finder->findByInstanceId($identifier->getInstanceId());

        if (is_null($application)) {
            return null;
        }

        $manifest          = $application->getManifest();
        $manifestConverter = new Domain\AppManifest\Converter();
        $accessRules       = $manifestConverter->convertToAccessRuleList($manifest);

        if (empty($accessRules)) {
            return null;
        }

        /** @var Domain\AppStorage\AccessOptions[] $found */
        $found     = [];
        $stateName = $identifier->getName();
        foreach ($accessRules as $rule) {
            if ($rule->matchesStateName($stateName)) {
                $found[] = $rule->getAccessOptions();
            }
        }

        if (1 === count($found)) {
            return array_pop($found);
        }

        return null;
    }

    /**
     * @param Domain\AppStorageItemIdentifier $identifier
     * @param ServiceAccessRequest      $request
     * @param $value
     *
     * @return Domain\AppStorageItem
     */
    public function writeValue( Domain\AppStorageItemIdentifier $identifier, ServiceAccessRequest $request, $value)
    {
        /** @var \Exception $exception */
        $exception = null;
        try {
            return $this->changeValue($identifier, $request, $value);
        } catch (\Exception $e) {
            $exception = $e;
        }

        // update failed because the state was not found, so we'll try to create it
        if (
            $exception instanceof Domain\AppStorage\Exception
            && $exception->getCode() === Domain\AppStorage\Exception::CODE_STATE_NOT_FOUND
        ) {
            return $this->addValue($identifier, $request, $value);
        }

        throw $exception;
    }

    /**
     * @param Domain\AppStorageItemIdentifier $identifier
     * @param ServiceAccessRequest      $request
     * @param $value
     *
     * @return Domain\AppStorageItem
     */
    public function addValue( Domain\AppStorageItemIdentifier $identifier, ServiceAccessRequest $request, $value)
    {
        /** @var Entity\AppStore\AppInstance $appInstance */
        $appInstance = $this->entityManager->find(Entity\AppStore\AppInstance::class, $identifier->getInstanceId());
        if (!$appInstance) {
            $exception = Domain\AppStorage\Exception::createStateNotFoundException('missing application');
            throw $exception;
        }

        $accessOptions = $this->findAccessOptions($identifier);
        if (is_null($accessOptions)) {
            $exception = Domain\AppStorage\Exception::createAccessRuleNotFoundException();
            throw $exception;
        }

        $stateEntity = new Entity\AppStore\AppState();
        // relationships
        if (!$accessOptions->isWorldAccessible()) {
            $stateEntity->setOwner($request->getAuthPerson());
        }
        $stateEntity->setAppInstance($appInstance);
        // identity properties
        $stateEntity->setName($identifier->getName());
        $stateEntity->setEntityId($identifier->getEntityId());
        // access properties
        $stateEntity->setPermRead($accessOptions->getReadPermission());
        $stateEntity->setPermWrite($accessOptions->getWritePermission());
        $stateEntity->setIsBackendOnly($accessOptions->isBackendOnly());
        // value properties
        $stateEntity->setValue($value);
        $stateEntity->setValueType('object');

        $converter   = new StateEntityConverter();
        $stateObject = $converter->toDomainObject($stateEntity);
        if (!$stateObject) {
            throw new \DomainException('could not convert state entity to a domain object');
        }

        if ($this->allowWriteAccess($request, $stateObject)) {
            $this->entityManager->persist($stateEntity);
            $this->entityManager->flush();

            return $stateObject;
        }

        $exception = Domain\AppStorage\Exception::createAccessDeniedException();
        throw $exception;
    }

    /**
     * @param Domain\AppStorageItemIdentifier $identifier
     * @param ServiceAccessRequest      $request
     * @param string                    $value
     *
     * @return Domain\AppStorageItem
     */
    public function changeValue( Domain\AppStorageItemIdentifier $identifier, ServiceAccessRequest $request, $value)
    {
        if ($request->getAccessLevel() !== Domain\Constants::ACCESS_LEVEL_WRITE) {
            $exception = Domain\AppStorage\Exception::createAccessDeniedException();
            throw $exception;
        }

        $accessOptions = $this->findAccessOptions($identifier);
        if (!$accessOptions) {
            $exception = Domain\AppStorage\Exception::createStateNotFoundException();
            throw $exception;
        }

        $finder      = new StateEntityFinder($this->entityManager, $this->queryBuilder);
        $stateEntity = $finder->findOne($identifier, $accessOptions, $request);

        if (is_null($stateEntity)) {
            $exception = Domain\AppStorage\Exception::createStateNotFoundException();
            throw $exception;
        }

        $converter = new StateEntityConverter();
        $state     = $converter->toDomainObject($stateEntity);

        if ($this->allowWriteAccess($request, $state)) {
            $stateEntity->setValue($value);
            $this->entityManager->persist($stateEntity);
            $this->entityManager->flush();

            return $converter->toDomainObject($stateEntity);
        }

        $exception = Domain\AppStorage\Exception::createAccessDeniedException();
        throw $exception;
    }

    /**
     * @param Domain\AppStorageItemIdentifier $identifier
     * @param ServiceAccessRequest      $request
     *
     * @return string
     */
    public function readValue( Domain\AppStorageItemIdentifier $identifier, ServiceAccessRequest $request)
    {
        if ($request->getAccessLevel() !== Domain\Constants::ACCESS_LEVEL_READ) {
            $exception = Domain\AppStorage\Exception::createAccessDeniedException();
            throw $exception;
        }

        $accessOptions = $this->findAccessOptions($identifier);
        if (!$accessOptions) {
            $exception = Domain\AppStorage\Exception::createStateNotFoundException();
            throw $exception;
        }

        $finder      = new StateEntityFinder($this->entityManager, $this->queryBuilder);
        $stateEntity = $finder->findOne($identifier, $accessOptions, $request);

        if (empty($stateEntity)) {
            $exception = Domain\AppStorage\Exception::createStateNotFoundException();
            throw $exception;
        }

        $converter = new StateEntityConverter();
        $state     = $converter->toDomainObject($stateEntity);

        if ($this->allowReadAccess($request, $state)) {
            return $state->getValue();
        }

        $exception = Domain\AppStorage\Exception::createAccessDeniedException();
        throw $exception;
    }

    /**
     * @param Domain\AppStorageSearchFilter $filter
     * @param ServiceAccessRequest                $request
     *
     * @return Domain\AppStorageItem[]|array
     */
    public function readAllValues( Domain\AppStorageSearchFilter $filter, ServiceAccessRequest $request)
    {
        $entities = [];

        $findOwned   = $this->queryBuilder->buildFindOwnedStateQuery($this->entityManager, $request->getAuthPerson(), $filter);
        $newEntities = $findOwned->getResult();
        $entities    = array_merge($entities, $newEntities);

        $accessPermission = new Domain\AppStorage\AccessPermission(Domain\Constants::ACCESS_LEVEL_READ, Domain\Constants::PERMISSION_EVERYONE);
        $filter->setAccessPermission($accessPermission);

        $findQuery   = $this->queryBuilder->buildFindOwnedByNobodyStateQuery($this->entityManager, $filter);
        $newEntities = $findQuery->getResult();
        $entities    = array_merge($entities, $newEntities);

        $findQuery   = $this->queryBuilder->buildFindOwnedByOtherStateQuery($this->entityManager, $request->getAuthPerson(), $filter);
        $newEntities = $findQuery->getResult();
        $entities    = array_merge($entities, $newEntities);

        $converter = new StateEntityConverter();
        /** @var string[] $allowedValues */
        $allowedValues = [];
        foreach ($entities as $entity) {
            $state = $converter->toDomainObject($entity);
            if ($this->allowReadAccess($request, $state)) {
                $allowedValues[] = $state;
            }
        }

        return $allowedValues;
    }

    /**
     * @param Domain\AppStorageItemIdentifier $identifier
     * @param ServiceAccessRequest      $request
     *
     * @return string
     */
    public function removeValue( Domain\AppStorageItemIdentifier $identifier, ServiceAccessRequest $request)
    {
        if ($request->getAccessLevel() !== Domain\Constants::ACCESS_LEVEL_WRITE) {
            $exception = Domain\AppStorage\Exception::createAccessDeniedException();
            throw $exception;
        }

        $accessOptions = $this->findAccessOptions($identifier);
        if (!$accessOptions) {
            $exception = Domain\AppStorage\Exception::createStateNotFoundException();
            throw $exception;
        }

        $finder      = new StateEntityFinder($this->entityManager, $this->queryBuilder);
        $stateEntity = $finder->findOne($identifier, $accessOptions, $request);

        if (is_null($stateEntity)) {
            $exception = Domain\AppStorage\Exception::createStateNotFoundException();
            throw $exception;
        }

        $converter = new StateEntityConverter();
        $state     = $converter->toDomainObject($stateEntity);

        if ($this->allowWriteAccess($request, $state)) {
            $this->entityManager->remove($stateEntity);
            $this->entityManager->flush();

            return $state->getValue();
        }

        $exception = Domain\AppStorage\Exception::createAccessDeniedException();
        throw $exception;
    }
}
