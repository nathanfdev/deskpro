<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Generator\Exporter\Parser\ZenDesk;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\FormatterInterface;
use Application\ImportBundle\Reader\ZenDesk\ZenDeskReaderInterface;
use Guzzle\Http\Client as HttpClient;

/**
 * ZenDesk articles parser
 *
 * Class Articles
 * @package Application\ImportBundle\Generator\Exporter\Parser\ZenDesk
 */
final class Articles extends AbstractParser
{
    /**
     * @var ArticlePeopleStorage
     */
    private $article_people;

    /**
     * @var OidMapper
     */
    private $article_mapper;

    /**
     * @var HttpClient
     */
    private $http_client;

    /**
     * Constructor
     *
     * @param ZenDeskReaderInterface       $reader
     * @param FormatterInterface           $formatter
     * @param ParserPeopleStorageInterface $people_storage
     * @param OidMapper                    $article_mapper
     * @param HttpClient                   $http_client
     */
    public function __construct(
        ZenDeskReaderInterface       $reader,
        FormatterInterface           $formatter,
        ParserPeopleStorageInterface $people_storage,
        OidMapper                    $article_mapper,
        HttpClient                   $http_client
    ) {
        parent::__construct($reader, $formatter);

        $this->article_people = $people_storage;
        $this->article_mapper = $article_mapper;
        $this->http_client    = $http_client;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_ARTICLE;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        // We can read data from ZD reader twice because of ZD reader cache support
        return count($this->getArticles());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        return new Entity\Collection();
    }

    /**
     * Returns articles
     * Loads data from ZenDesk reader
     *
     * @return array
     * @throws \Exception
     */
    private function getArticles()
    {
        $this->logDebugTimeStart('getArticles', "Reading articles batch");
        return array();
    }
}
