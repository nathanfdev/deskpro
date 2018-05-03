<?php

namespace Application\DeskPRO\Entity\EventListener;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\LogEvent;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\HttpFoundation\Session;
use Application\DeskPRO\Log\Event\Base as BaseLogEvent;
use Application\DeskPRO\ORM\StateChange\StateChangeRecorder;
use Application\DeskPRO\People\PersonGuest;
use Application\LegacyApiBundle\Request\RequestAuth;
use Symfony\Component\DependencyInjection\Exception\InactiveScopeException;

abstract class EntityChangeLogListener
{
    /** @var DeskproContainer */
    protected $container;

    /** @var array */
    protected $queued_inserts = [];

    /** @var array */
    protected $queued_updates = [];

    /** @var array */
    protected $queued_deletions = [];

    /** @var \Application\DeskPRO\Monolog\Logger */
    protected $logger;

    public function __construct(DeskproContainer $container)
    {
        $this->container = $container;
        $this->logger    = $container->get('deskpro.logger.changelog');
    }

    /**
     * todo backend context?
     */
    protected function getContextPerson()
    {
        $person = $this->tryToGetPersonFromContext();

        // don't even return a PersonGuest
        if (!$person || $person instanceof PersonGuest) {
            return;
        }

        return $person;
    }

    protected function tryToGetPersonFromContext()
    {
        $c = $this->container;
        /* @var RequestAuth $auth */
        try {
            if ($c->has('deskpro.api.request_auth') && ($auth = $c->get('deskpro.api.request_auth'))) {
                if ($apiUser = $auth->getApiUser()) {
                    if ($apiUser->person) {
                        return $apiUser->person;
                    }
                }
            }

            if ($c->has('session') && ($sess = $c->get('session')) && $sess instanceof Session) {
                /* @var $sess Session */
                if ($person = $sess->getPerson()) {
                    return $person;
                }
            }
        } catch (InactiveScopeException $e) {
        }
    }

    protected function tryToGetApiKeyFromContext()
    {
        $c = $this->container;
        /* @var RequestAuth $auth */
        try {
            if ($c->has('deskpro.api.request_auth') && ($auth = $c->get('deskpro.api.request_auth'))) {
                if ($apiUser = $auth->getApiUser()) {
                    return $apiUser->api_key;
                }
            }
        } catch (InactiveScopeException $e) {
        }
    }

    /**
     * @param DomainObject $entity
     *
     * @return array
     */
    protected function getChangesForEntity(DomainObject $entity)
    {
        $ret = [];

        /** @var StateChangeRecorder $stateChangeRecorder */
        $stateChangeRecorder = $entity->getStateChangeRecorder();
        if (!$changes = $stateChangeRecorder->getChanges()) {
            return $ret;
        }

        foreach ($changes as $change) {
            if ($change->isSame() || !isset($this->fields[$change->getField()])) {
                continue;
            }

            $ret[$change->getField()] = $change;
        }

        return $ret;
    }

    /**
     * @param BaseLogEvent $event
     * @param Person|null  $performer
     *
     * @return LogEvent
     */
    protected function createLogEntry(BaseLogEvent $event, Person $performer = null)
    {
        $performer = $this->getContextPerson() ?: $performer;
        $key       = $this->tryToGetApiKeyFromContext();

        return new LogEvent($event, $performer, $key);
    }

    /**
     * @param DomainObject $entity
     */
    protected function flush(DomainObject $entity)
    {
        $oid = spl_object_hash($entity);

        foreach (['inserts', 'updates', 'deletions'] as $type) {
            $this->doFlush($oid, $type);
        }
    }

    /**
     * @param $oid
     * @param $type
     */
    protected function doFlush($oid, $type)
    {
        if (!isset($this->{'queued_'.$type}[$oid])) {
            return;
        }

        $entry = $this->{'queued_'.$type}[$oid];
        $this->logger->info($entry);
        foreach ($entry->children as $child) {
            $this->logger->info($child);
        }

        unset($this->{'queued_'.$type}[$oid]);
    }
}
