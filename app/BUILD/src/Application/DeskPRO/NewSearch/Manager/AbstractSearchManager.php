<?php

namespace Application\DeskPRO\NewSearch\Manager;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\EntityRepository\Ticket as TicketRepository;
use Application\DeskPRO\NewSettings\SettingsBag;
use Application\DeskPRO\Settings\Settings;
use Doctrine\ORM\EntityManager;
use Orb\Util\Arrays;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class AbstractSearchManager.
 */
abstract class AbstractSearchManager implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * The currently logged in person.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * Entity Manager.
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * DeskPRO settings.
     *
     * @var SettingsBag
     */
    protected $settings;

    /**
     * Object to Entity mappings.
     *
     * @var array
     */
    protected $objects = [
        'article'      => 'DeskPRO:Article',
        'chat'         => 'DeskPRO:ChatConversation',
        'download'     => 'DeskPRO:Download',
        'feedback'     => 'DeskPRO:Feedback',
        'news'         => 'DeskPRO:News',
        'organization' => 'DeskPRO:Organization',
        'person'       => 'DeskPRO:Person',
        'ticket'       => 'DeskPRO:Ticket',
        'topic'        => 'DeskPRO:Topic',
    ];

    /**
     * Objects which require permissions.
     *
     * @var array
     */
    protected $restrictedObjects = [
        'ticket',
        'chat',
    ];

    /**
     * Search results.
     *
     * @var array
     */
    protected $results = [];

    /**
     * @param $person
     */
    public function setPersonContext($person)
    {
        $this->person = $person;
    }

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * $return EntityManager $entityManager.
     */
    public function getEntityManager()
    {
        if (is_null($this->entityManager)) {
            $this->setEntityManager(
                $this->container->get('doctrine.orm.default_entity_manager')
            );
        }

        return $this->entityManager;
    }

    /**
     * @param Settings $settings
     */
    public function setSettings(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return SettingsBag
     */
    public function getSettings()
    {
        if (is_null($this->settings)) {
            $this->setSettings(
                $this->container->get('deskpro.core.settings')
            );
        }

        return $this->settings;
    }

    /**
     * @param TicketRepository $entityRepository
     * @param array            $matcher
     */
    protected function getTicketByRefOrId(TicketRepository $entityRepository, array $matcher)
    {
        $ticketPermissionsChecker = null;
        if ($this->person instanceof Person && in_array($matcher['object'], $this->restrictedObjects)) {
            /** @var \Application\DeskPRO\People\PermissionChecker\TicketChecker $ticketPermissionsChecker */
            $ticketPermissionsChecker = $this->person->getPermissionsManager()->get('TicketChecker');
        }

        if ($matcher['field'] === 'ref') {
            if (($entity = $entityRepository->findTicketRef(strtoupper($matcher['param']))) instanceof Ticket) {
                if (!is_null($ticketPermissionsChecker) &&
                    $ticketPermissionsChecker->canView($entity)) {
                    $this->handleResult($matcher['object'], $entity);
                }
            } elseif (strlen($matcher['param']) >= 3) {
                foreach ($entityRepository->searchTicketRef($matcher['param']) as $entity) {
                    if (!is_null($ticketPermissionsChecker) &&
                        $ticketPermissionsChecker->canView($entity)) {
                        $this->handleResult($matcher['object'], $entity);
                    }
                }
            }
        } else {
            $entity = $entityRepository->findTicketId($matcher['param']);
            if ($entity && !is_null($ticketPermissionsChecker) && $ticketPermissionsChecker->canView($entity)) {
                $this->handleResult($matcher['object'], $entity);
            }
        }
    }

    /**
     * @param TicketRepository $entityRepository
     * @param array            $matcher
     */
    protected function getSimpleTicketById(TicketRepository $entityRepository, array $matcher)
    {
        $ticketPermissionsChecker = null;
        if ($this->person instanceof Person) {
            /** @var \Application\DeskPRO\People\PermissionChecker\TicketChecker $ticketPermissionsChecker */
            $ticketPermissionsChecker = $this->person->getPermissionsManager()->get('TicketChecker');
        }

        /** @var Ticket|null $entity */
        $entity = $entityRepository->find($matcher['param']);
        if (!is_null($ticketPermissionsChecker) &&
            $ticketPermissionsChecker->canView($entity)) {
            $this->handleResult($matcher['object'], $entity);
        }
    }

    /**
     * @param $object
     *
     * @return bool
     */
    protected function isAllowed($object)
    {
        $isAllowed = true;

        switch ($object) {
            case 'person':
            case 'organization':
                $isAllowed = $this->person->hasPerm('agent_people.use');
                break;
        }

        return $isAllowed;
    }

    /**
     * Remove object we don't need to search for.
     *
     * @param array $limitObjects
     */
    protected function limitResultingObjects(array $limitObjects = [])
    {
        if (count($limitObjects) > 0) {
            $objects = array_map(function ($object) {
                return [$object => $this->objects[$object]];
            }, $limitObjects);

            $this->objects = Arrays::collapse($objects);
        }
    }

    /**
     * @param string $object
     * @param mixed  $result
     */
    protected function handleResult($object, $result)
    {
        // Backward compatibility with old code
        $object = ($object === 'chat') ? 'chat_conversation' : $object;

        if (!array_key_exists($object, $this->results)) {
            $this->results[$object] = [];
        }

        $this->results[$object] = array_merge(
            $this->results[$object],
            is_array($result) ? $result : [$result]
        );
    }

    /**
     * @param bool $pushBack
     *
     * @return array
     */
    protected function prepareResults()
    {
        $this->results = array_map(function ($group) {
            return Arrays::uniqueObjectArray($group);
        }, $this->results);

        return $this->results;
    }

    /**
     * @param string $object
     *
     * @return bool
     */
    protected function requiresPermission($object)
    {
        return in_array($object, $this->restrictedObjects);
    }

    /**
     * @param null|string $query
     *
     * @return bool
     */
    protected function proceedWithSearch($query = null)
    {
        return !(is_null($query) || (is_string($query) && trim($query) === ''));
    }
}
