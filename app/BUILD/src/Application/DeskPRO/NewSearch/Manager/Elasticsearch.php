<?php

namespace Application\DeskPRO\NewSearch\Manager;

use Application\DeskPRO\Elastica\ClientFactory;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\NewSearch\Manager\Traits\ExtractsMatchersFromQuery;
use DpSys\LowError\SystemErrorHandler;
use Elastica\Response;
use FOS\ElasticaBundle\Manager\RepositoryManager;
use Orb\Util\Arrays;
use Orb\Util\Numbers;
use Orb\Validator\StringEmail;

/**
 * Class Elasticsearch.
 */
class Elasticsearch extends AbstractSearchManager implements SearchManagerInterface
{
    use ExtractsMatchersFromQuery;

    /**
     * @param null|string $query
     * @param null|string $sort
     * @param array       $limitTypes
     *
     * @return array|mixed
     */
    public function quickSearch($query = null, $sort = null, array $limitTypes = [])
    {
        // check if we need to proceed
        if (!$this->proceedWithSearch($query)) {
            return array_map(function ($object) {
                return [$object => []];
            }, array_keys($this->objects));
        }

        // limit searchable object
        $this->limitResultingObjects($limitTypes);

        // Check for an URL first as we don't event need elastica for it
        $matchers = $this->extractMatchersFromQuery($query);
        if (count($matchers) > 0) {
            foreach ($this->extractMatchersFromQuery($query) as $matcher) {
                // check if person has access to an object
                if (!$this->isAllowed($matcher['object'])) {
                    continue;
                }

                // as we operate only with ids, refs and slugs, we don't need elasticsearch here
                $entityRepository = $this->getEntityManager()
                    ->getRepository($this->objects[$matcher['object']]);

                /*
                 * For tickets search we use custom logic in order to utilize
                 * findTicketRef(), SearchTicketRef() and findTicketId() methods,
                 * and check ticket permissions for logged in user if any.
                 *
                 * @see AbstractSearchManager::getTicketByRefOrId()
                 */
                if ($matcher['object'] === 'ticket') {
                    $this->getTicketByRefOrId($entityRepository, $matcher);
                } else {
                    /**
                     * All other objects could simply found by id or slug,
                     * so no specific logic needed here.
                     */
                    $entity = $entityRepository->findOneBy([
                        $matcher['field'] => $matcher['param'],
                    ]);

                    /*
                     * Add to results set if it's not null
                     *
                     * @todo: maybe better use instanceof, but this will
                     *        require more complex workaround, so not sure
                     *        it does matter that much to impact the timings.
                     */
                    if (!is_null($entity)) {
                        $this->handleResult($matcher['object'], $entity);
                    }
                }
            }

            return [$this->prepareResults(), [], false];
        }

        /*
         * Time for ES to perform fulltext search
         */

        // define sorting order
        if (!is_null($sort) && !in_array($sort, ['score', 'date_active', 'date_created'])) {
            $sort = 'score';
        }

        /** @var RepositoryManager $repositoryManager */
        $repositoryManager = $this->container->get('fos_elastica.manager');

        // go over objects
        foreach ($this->objects as $object => $entityClass) {
            // check if person has access to an object
            if (!$this->isAllowed($object)) {
                continue;
            }

            // get ES entity repository
            $elsentRepository = $repositoryManager->getRepository($entityClass);

            // to operate with exact matches like ref, id or slug we don't need elasticsearch
            $entityRepository = $this->getEntityManager()->getRepository($entityClass);

            // check if object requires permissions and set person context
            if ($this->requiresPermission($object)) {
                $elsentRepository->setPersonContext($this->person);
            }

            // Specific logic for tickets
            if ($object === 'ticket') {
                // Lookup by id or ref
                if (Numbers::isInteger($query) || preg_match('#^[0-9A-Z\-_\.]+$#', $query)) {
                    // use custom lookup logic
                    $this->getTicketByRefOrId($entityRepository, [
                        'object' => 'ticket',
                        'field'  => Numbers::isInteger($query) ? 'id' : 'ref',
                        'param'  => $query,
                    ]);
                } else {
                    $result = $elsentRepository->find($query, null, [
                        'sort_type' => $sort,
                    ]);
                    $this->handleResult($object, $result);
                }
            } elseif ($object === 'person') {
                // Custom logic for person

                if (Numbers::isInteger($query)) {
                    $entity = $entityRepository->findById($query);

                    if ($entity instanceof Person) {
                        $this->handleResult($object, $entity);
                    }
                } elseif (StringEmail::isValueValid($query)) {
                    $entity = $this->container->getSystemService('UsersourceManager')
                        ->findPersonByEmail($query);

                    if ($entity instanceof Person) {
                        $this->handleResult($object, $entity);
                    }
                } else {
                    $result = $elsentRepository->find($query, null, [
                        'sort_type' => $sort,
                    ]);
                    $this->handleResult($object, $result);
                }
            } else {
                /*
                 * All other objects do not require any specific logic,
                 * so just standard cases
                 */

                // Lookup by id
                if (Numbers::isInteger($query)) {
                    $entity = $entityRepository->findById($query);
                    if (!is_null($entity)) {
                        $this->handleResult($object, $entity);
                    }
                } else {
                    $result = $elsentRepository->find($query, null, [
                        'sort_type' => $sort,
                    ]);
                    $this->handleResult($object, $result);
                }
            }
        }

        return [$this->prepareResults(), [], false];
    }

    /**
     * @param string $url
     *
     * @return string|null
     */
    public function getVersion($url = null)
    {
        if ((is_null($url) || (is_string($url) && trim($url) === ''))) {
            $url = $this->getSettings()->get('elastica.clients.default.url');
        }

        $version = null;

        try {
            $client = $this->container
                ->get('deskpro.elastica.client_factory')
                ->createClientByConfig(ClientFactory::createConfigFromUrl($url));

            if (($response = $client->request('/')) instanceof Response) {
                $version = Arrays::get($response->getData(), 'version.number', null);
            }
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
        }

        return $version;
    }

    /**
     * @param null|string $url
     *
     * @throws \Exception
     */
    public function testVersion($url = null)
    {
        $version = $this->getVersion($url);

        if (is_null($version)) {
            throw new \Exception('Unable to get ElasticSearch version.');
        }
        if (version_compare($version, '2.0.0') < 0 || version_compare($version, '6.0.0') >= 0) {
            throw new \Exception(
                "Deskpro is not compatible with your ElasticSearch {$version} server. 
                Please use DeskPRO with an ElasticSearch 2.x or 5.x server."
            );
        }
    }
}
