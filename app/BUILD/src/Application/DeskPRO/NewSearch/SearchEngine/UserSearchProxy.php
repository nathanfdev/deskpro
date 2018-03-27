<?php

namespace Application\DeskPRO\NewSearch\SearchEngine;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use DpSys\LowError\SystemErrorHandler;

class UserSearchProxy implements UserSearchInterface
{
    /** @var Elastic\UserSearch */
    protected $es;

    /** @var Mysql\UserSearch */
    protected $dbs;

    /** @var DeskproContainer */
    protected $c;

    public function __construct(DeskproContainer $container)
    {
        $this->c = $container;
    }

    /**
     * @param SearchContextInterface $context
     * @param string                 $query
     * @param array|null             $options
     *
     * @return Result\ResultSet
     */
    public function search(SearchContextInterface $context, $query, array $options = null)
    {
        if ($this->c->getSetting('elastica.enabled')) {
            try {
                try {
                    return $this->es()->search($context, $query, $options);
                } catch (\Exception $e) {
                    $elasticsearch = $this->c->get('deskpro.search_manager.elasticsearch');
                    $elasticsearch->testVersion();

                    throw $e;
                }
            } catch (\Exception $e) {
                SystemErrorHandler::logException($e);

                // fallback on DB search
                return $this->dbs()->search($context, $query, $options);
            }
        } else {
            return $this->dbs()->search($context, $query, $options);
        }
    }

    /**
     * @param SearchContextInterface $context
     * @param string                 $content
     * @param array                  $options
     *
     * @return ResultSet
     */
    public function similarTo(SearchContextInterface $context, $content, array $options = null)
    {
        if ($this->c->getSetting('elastica.enabled')) {
            try {
                return $this->es()->similarTo($context, $content, $options);
            } catch (\Exception $e) {
                SystemErrorHandler::logException($e);

                // fallback on DB search
                return $this->dbs()->similarTo($context, $content, $options);
            }
        } else {
            return $this->dbs()->similarTo($context, $content, $options);
        }
    }

    /**
     * @throws \Symfony\Component\DependencyInjection\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\ServiceNotFoundException
     *
     * @return Elastic\UserSearch|SearchEngine
     */
    public function es()
    {
        if ($this->es) {
            return $this->es;
        }

        return $this->es = $this->es = new Elastic\UserSearch(
            $this->c->get('fos_elastica.index.deskpro'),
            new Elastic\ElasticaResultsTransformer($this->c->getEm())
        );
    }

    /**
     * @return Mysql\UserSearch|SearchEngine
     */
    public function dbs()
    {
        if ($this->dbs) {
            return $this->dbs;
        }

        return $this->dbs = new Mysql\UserSearch(
            $this->c->getDbRead('search.searcher.content'),
            $this->c->getEm(),
            new Mysql\MysqlResultsTransformer($this->c->getEm())
        );
    }
}
