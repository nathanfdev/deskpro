<?php

namespace Application\DeskPRO\NewSearch\Manager;

use Application\DeskPRO\Elastica\ClientFactory;
use Application\DeskPRO\EntityRepository\Ticket;
use Elastica\Response;
use FOS\ElasticaBundle\Manager\RepositoryManager;
use Orb\Util\Arrays;
use Orb\Util\Numbers;
use Orb\Validator\StringEmail;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Elasticsearch Search Manager.
 */
class Elasticsearch implements SearchManagerInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * The currently logged in person.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * Objects to search.
     *
     * @var array
     */
    protected $objects = [
        'article'           => 'DeskPRO:Article',
        'download'          => 'DeskPRO:Download',
        'feedback'          => 'DeskPRO:Feedback',
        'news'              => 'DeskPRO:News',
        'topic'             => 'DeskPRO:Topic',
        'ticket'            => 'DeskPRO:Ticket',
        'person'            => 'DeskPRO:Person',
        'organization'      => 'DeskPRO:Organization',
        'chat_conversation' => 'DeskPRO:ChatConversation',
    ];

    /**
     * Permission requirement.
     *
     * @var array
     */
    protected $requiresPermission = [
        'ticket',
    ];

    /**
     * Search results.
     *
     * @var array
     */
    protected $results = [];

    public function quickSearch($q, $sort = null, array $limit_types = null)
    {
        $result_meta = [];
        $people_top  = false;

        if ($sort && !in_array($sort, ['score', 'date_active', 'date_created'])) {
            $sort = null;
        }
        if (!$sort) {
            $sort = 'score';
        }

        /** @var RepositoryManager $repositoryManager */
        $repositoryManager = $this->container->get('fos_elastica.manager');

        foreach ($this->objects as $object => $model) {
            if ($limit_types !== null && !in_array($object, $limit_types)) {
                continue;
            }

            if (!$this->isAllowed($object)) {
                continue;
            }

            $repository = $repositoryManager->getRepository($model);
            $ent_repos  = $this->container->getEm()->getRepository($model);

            if ($this->requiresPermission($object)) {
                $repository->setPersonContext($this->person);
            }

            if ($model == 'DeskPRO:Ticket' && preg_match('#^[0-9A-Z\-_\.]+$#', $q)) {
                /** @var Ticket $ent_repos */
                if ($ticket = $ent_repos->findTicketRef(strtoupper($q))) {
                    if ($this->person->PermissionsManager->TicketChecker->canView($ticket)) {
                        $this->handleResult($object, $ticket);
                    }
                } elseif (strlen($q) >= 3) {
                    $tickets = $ent_repos->searchTicketRef($q);
                    $results = [];
                    foreach ($tickets as $ticket) {
                        if ($this->person->PermissionsManager->TicketChecker->canView($ticket)) {
                            $results[] = $ticket;
                        }
                    }
                    $this->handleResult($object, $results);
                }
            }

            if (Numbers::isInteger($q)) {
                if ($model == 'DeskPRO:Ticket' && $this->person) {
                    $result = $ent_repos->findTicketId($q);

                    if ($result) {
                        $this->person->loadHelper('PermissionsManager');
                        if (!$this->person->PermissionsManager->TicketChecker->canView($result)) {
                            $result = null;
                        }
                    }
                } else {
                    $result = $ent_repos->findById($q);
                }
                if ($result) {
                    $this->handleResult($object, $result);
                }
            }

            if ($model == 'DeskPRO:Person' && StringEmail::isValueValid($q)) {
                $result = $this->container->getSystemService('UsersourceManager')->findPersonByEmail($q);
                if ($result) {
                    $this->handleResult($object, $result);
                }
            }

            $result = $repository->find($q, null, [
                'sort_type' => $sort,
            ]);
            if ($result) {
                $this->handleResult($object, $result);
            }
        }

        $this->results = array_map(function ($group) {
            return Arrays::uniqueObjectArray($group);
        }, $this->results);

        return [$this->results, $result_meta, $people_top];
    }

    private function isAllowed($object)
    {
        switch ($object) {

            case 'person':
            case 'organization':
                return $this->person->hasPerm('agent_people.use');

            default:
                return true;

        }
    }

    private function handleResult($object, $result)
    {
        if (!isset($this->results[$object])) {
            $this->results[$object] = [];
        }

        if (!is_array($result)) {
            $result = [$result];
        }

        switch ($object) {
            default:
                $this->results[$object] = array_merge($this->results[$object], $result);
        }
    }

    private function requiresPermission($object)
    {
        return in_array($object, $this->requiresPermission);
    }

    public function setPersonContext($person)
    {
        $this->person = $person;
    }

    /**
     * @param string $url
     *
     * @return string|null
     */
    public function getVersion($url = '')
    {
        if (!$url) {
            $url = $this->container->get('deskpro.core.settings')->get('elastica.clients.default.url');
        }

        try {
            $config        = ClientFactory::createConfigFromUrl($url);
            $clientFactory = $this->container->get('deskpro.elastica.client_factory');
            $client        = $clientFactory->createClientByConfig($config);
            $res           = $client->request('/');
            if ($res instanceof Response) {
                $res     = $res->getData();
                $version = isset($res['version']['number']) ? $res['version']['number'] : null;

                return $version;
            }
        } catch (\Exception $e) {
        }

        return;
    }

    /**
     * @param string $url
     *
     * @throws \Exception
     */
    public function testVersion($url = '')
    {
        $version = $this->getVersion($url);

        if (!$version) {
            throw new \Exception('Unable to get ElasticSearch version.');
        }
        if (version_compare($version, '2.0.0') < 0 || version_compare($version, '6.0.0') >= 0) {
            throw new \Exception("Deskpro is not compatible with your ElasticSearch $version server. Please use DeskPRO with an ElasticSearch 2.x or 5.x server.");
        }
    }
}
