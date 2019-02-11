<?php

namespace DeskPRO\Bundle\AuditBundle\EventListener;

use DeskPRO\Bundle\AuditBundle\Configuration\AuditContext;
use DeskPRO\Bundle\AuditBundle\Event\LogEvent;
use DeskPRO\Bundle\AuditBundle\Log\AuditLogHelper;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\UnitOfWork;
use Orb\Util\Arrays;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class AuditListener.
 */
class AuditListener
{
    const ALL    = 'all'; // this is special key to reduce code duplicate in configuration
    const INSERT = 'insert';
    const UPDATE = 'update';
    const REMOVE = 'remove';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var UnitOfWork
     */
    private $uow;

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * @var AuditLogHelper
     */
    private $auditLogHelper;

    /**
     * @var array
     */
    private $insertions = [];

    /**
     * @var array
     */
    private $updates = [];

    /**
     * @var array
     */
    private $deletions = [];

    /**
     * @var bool
     */
    private $enabled = true;

    /**
     * AuditListener constructor.
     *
     * @param EventDispatcherInterface $dispatcher
     * @param AuditLogHelper           $auditLogHelper
     */
    public function __construct(EventDispatcherInterface $dispatcher, AuditLogHelper $auditLogHelper)
    {
        $this->dispatcher     = $dispatcher;
        $this->auditLogHelper = $auditLogHelper;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Disables the listener (it wont log changes).
     */
    public function disableListener()
    {
        $this->enabled = false;
    }

    /**
     * Enables the listener.
     */
    public function enableListener()
    {
        $this->enabled = true;
    }

    public function preFlush(PreFlushEventArgs $eventArgs)
    {
        $this->em  = $eventArgs->getEntityManager();
        $this->uow = $this->em->getUnitOfWork();
    }

    /**
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        if (!$this->enabled) {
            return;
        }

        $this->insertions = $this->uow->getScheduledEntityInsertions();
        $this->updates    = $this->uow->getScheduledEntityUpdates();
        $this->deletions  = $this->uow->getScheduledEntityDeletions();
        $this->processUpdates();
        $this->processDeletions();
    }

    /**
     * @param PostFlushEventArgs $event
     */
    public function postFlush(PostFlushEventArgs $event)
    {
        if (!$this->enabled) {
            return;
        }

        $this->processInsertions();
    }

    /**
     * Because we want to log entities are really inserted into DB to know their ID.
     */
    private function processInsertions()
    {
        foreach ($this->insertions as $entity) {
            if (!$this->auditLogHelper->supportedEntity($entity)) {
                return;
            }
            $this->doProcess(self::INSERT, $entity);
        }
        $this->dispatcher->dispatch(LogEvent::FINISH_ALL_EVENT);
    }

    private function processDeletions()
    {
        foreach ($this->deletions as $entity) {
            if (!$this->auditLogHelper->supportedEntity($entity)) {
                return;
            }
            $this->doProcess(self::REMOVE, $entity);
        }
        $this->dispatcher->dispatch(LogEvent::FINISH_ALL_EVENT);
    }

    private function processUpdates()
    {
        foreach ($this->updates as $entity) {
            if (!$this->auditLogHelper->supportedEntity($entity)) {
                return;
            }
            $this->doProcess(self::UPDATE, $entity);
        }
        $this->dispatcher->dispatch(LogEvent::FINISH_ALL_EVENT);
    }

    /**
     * @param $action
     * @param $entity
     *
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     */
    private function doProcess($action, $entity)
    {
        $changeSet = $this->getChangesSet($action, $entity);
        if (!$this->checkChangeSet($changeSet)) {
            return; // don't process this update if change set is false positive
        }
        $context  = $this->createContext($entity, $action, $changeSet);
        $logEvent = new LogEvent($context);

        $this->dispatcher->dispatch(LogEvent::PRE_LOG_EVENT, $logEvent);
        if ($logEvent->shouldLog()) {
            $logEvent = new LogEvent($context); // need this, cause propagation is stopped
            $log      = $this->auditLogHelper->createAuditLog();

            /** @var ClassMetadataInfo $metadata */
            $metadata = $this->em
                ->getMetadataFactory()
                ->getMetadataFor(ClassUtils::getRealClass(get_class($entity)));

            $logEvent->setLog($log)->setMetadata($metadata);

            $this->dispatcher->dispatch(LogEvent::START_LOG_EVENT, $logEvent);
            $this->dispatcher->dispatch(LogEvent::FINISH_LOG_EVENT, $logEvent);
        }

        // TODO [cloudspam] proper cloud spam checker/handling
        if (
            defined('DPC_IS_CLOUD')
            && DPC_DEMO_EXPIRE
            && !DPC_SITE_IS_APPROVED
        ) {
            if ($entity instanceof \Application\DeskPRO\Entity\Template && ($action === self::UPDATE || $action === self::INSERT) && strpos($entity->getName(), ':emails_') !== false) {
                $code = $entity->getTemplateCode();
                $code = \Orb\Util\Strings::decodeHtmlEntities($code);
                $code = \Orb\Util\Strings::decodeUnicodeEntities($code);

                if (stripos($code, 'http://') || stripos($code, 'https://')) {
                    $tpl = $entity->getName();
                    \DpShutdown::add(function () use ($tpl) {
                        $tmpdata = new \Application\DeskPRO\Entity\TmpData();
                        $tmpdata->setType('cancel_for_abuse');
                        $tmpdata->setData('message', 'Template was updated with a link: '.$tpl);
                        $tmpdata->date_expire = new \DateTime('+30 minutes');
                        $this->em->persist($tmpdata);
                        $this->em->flush();

                        $url = DP_MA_SERVER_SECURE.'/cloud/call/'.DPC_SITE_ID.'/'.$tmpdata->getCode();

                        try {
                            $client = new \Zend\Http\Client(null, ['timeout' => 15, 'sslverifypeer' => false]);
                            $client->setMethod(\Zend\Http\Request::METHOD_GET);
                            $client->setUri($url);
                            $r = $client->send();
                        } catch (\Exception $e) {
                            error_log('Failed to cancel site: '.$e->getMessage());
                        }
                    });
                }
            }
        }
    }

    private function checkChangeSet($changeSet)
    {
        foreach ($changeSet as $set) {
            if (is_array($set[0]) && is_array($set[1]) && !empty(Arrays::arrayDiffAssocRecursive($set[0], $set[1]))) {
                return true;
            } elseif ($set[0] !== $set[1]) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $action
     * @param $entity
     *
     * @return array
     */
    private function getChangesSet($action, $entity)
    {
        switch ($action) {
            case self::INSERT:
            case self::UPDATE:
                return $this->uow->getEntityChangeSet($entity);
            case self::REMOVE:
                $data      = $this->uow->getOriginalEntityData($entity);
                $changeSet = array_filter(
                    $data,
                    function ($item) {
                        return is_scalar($item);
                    }
                );
                foreach ($changeSet as &$item) {
                    $item = [$item, null];
                }

                return $changeSet;
            default:
                throw new \LogicException('The action you want to process in unsupported!');
        }
    }

    /**
     * @param object $entity    this could be any object Doctrine trying to persist
     * @param string $action
     * @param array  $changeSet
     *
     * @return AuditContext
     */
    private function createContext($entity, $action, $changeSet = [])
    {
        return new AuditContext($action, $this->auditLogHelper->getPerformer(), $entity, $changeSet);
    }
}
