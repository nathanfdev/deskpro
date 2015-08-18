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

namespace Application\ImportBundle\Reader\ZenDesk\Fixtures\HelpCenter;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Reader\ZenDesk\Fixtures\AbstractFixture;
use Application\ImportBundle\Reader\ZenDesk\Fixtures\FixturePrepareInterface;
use Application\ImportBundle\Reader\ZenDesk\Fixtures\PeopleLoader;
use Application\ImportBundle\Reader\ZenDesk\Request\ClientHelper\HelpCenter\ArticleCommentCreate;
use Application\ImportBundle\Reader\ZenDesk\Request\ClientHelper\HelpCenter\ArticleCreate;
use Application\ImportBundle\Reader\ZenDesk\Request\ClientHelper\HelpCenter\SectionsFindAll;
use DateTime;
use Zendesk\API\Client;
use Zendesk\API\ResponseException;

/**
 * ZenDesk article fixtures
 *
 * Class Articles
 * @package Application\ImportBundle\Reader\ZenDesk\Fixtures\HelpCenter
 */
final class Articles extends AbstractFixture implements FixturePrepareInterface
{
    /**
     * @var PeopleLoader
     */
    private $people_loader;

    /**
     * @var array
     */
    private $sections = array();

    /**
     * Constructor
     *
     * @param Client       $client
     * @param PeopleLoader $people_loader
     */
    public function __construct(Client $client, PeopleLoader $people_loader)
    {
        parent::__construct($client);
        $this->people_loader = $people_loader;
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
    public function prepare(DateTime $initial_time, DateTime $end_time)
    {
        try {
            $helper = new SectionsFindAll();
            $this->sections = $helper->request($this->client)->sections;

        } catch (ResponseException $e) {
            $this->handleResponseException('section');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createItem($num, DateTime $initial_time, DateTime $end_time)
    {
        if (empty($this->sections)) {
            throw new \RuntimeException('No help center section');
        }

        $section = $this->sections[rand(0, count($this->sections) - 1)];
        $helper  = new ArticleCreate(array(
            'id'      => $section->id,
            'article' => array(
                'title'       => 'Fake article ' . $num,
                'body'        => 'Fake article content',
                'author_id'   => $this->people_loader->getRandomPersonId(),
                'label_names' => array('label 1', 'label 2'),
            ),
        ));

        $response = $helper->request($this->client);
        $article  = $response->article;

        $this->logger->info('Article created successfully');
        $this->logger->debug(json_encode($article));

        for ($i = 1; $i <= 100; $i++) {
            try {
                $helper = new ArticleCommentCreate(array(
                    'id'      => $article->id,
                    'comment' => array(
                        'author_id' => $this->people_loader->getRandomPersonId(),
                        'body'      => 'Comment #' . $i,
                        'locale'    => 'en-us',
                    ),
                ));

                $response = $helper->request($this->client);

                $this->logger->info('Article comment created successfully');
                $this->logger->debug(json_encode($response->comment));

            } catch (ResponseException $e) {
                $this->handleResponseException('article comment');
            }
        }
    }
}
