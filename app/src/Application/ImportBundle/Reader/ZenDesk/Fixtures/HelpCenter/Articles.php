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
use Application\ImportBundle\Reader\ZenDesk\Fixtures\CoreAPI\PeopleLoader;
use Application\ImportBundle\Reader\ZenDesk\LocaleMapper;
use Application\ImportBundle\Reader\ZenDesk\Request\ClientHelper\HelpCenter\ArticleAttachmentCreate;
use Application\ImportBundle\Reader\ZenDesk\Request\ClientHelper\HelpCenter\ArticleCommentCreate;
use Application\ImportBundle\Reader\ZenDesk\Request\ClientHelper\HelpCenter\ArticleCreate;
use Application\ImportBundle\Reader\ZenDesk\Request\ClientHelper\HelpCenter\ArticleTranslationCreate;
use DateTime;
use Zendesk\API\Client;
use Zendesk\API\ResponseException;

/**
 * ZenDesk article fixtures
 *
 * Class Articles
 * @package Application\ImportBundle\Reader\ZenDesk\Fixtures\HelpCenter
 */
final class Articles extends AbstractFixture
{
    /**
     * @var PeopleLoader
     */
    private $people_loader;

    /**
     * @var SectionLoader
     */
    private $section_loader;

    /**
     * Constructor
     *
     * @param Client        $client
     * @param PeopleLoader  $people_loader
     * @param SectionLoader $section_loader
     */
    public function __construct(Client $client, PeopleLoader $people_loader, SectionLoader $section_loader)
    {
        parent::__construct($client);

        $this->people_loader  = $people_loader;
        $this->section_loader = $section_loader;
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
    protected function createItem($num, DateTime $initial_time, DateTime $end_time)
    {
        $helper  = new ArticleCreate(array(
            'id'      => $this->section_loader->getRandomSectionId(),
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

        for ($i = 1; $i <= 10; $i++) {
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

        for ($i = 1; $i <= 2; $i++) {
            try {
                $helper = new ArticleAttachmentCreate(array(
                    'id'     => $article->id,
                    'file'   => $this->getRandomUploadFile(),
                    'inline' => $this->getRandomBoolString(),
                ));

                $response = $helper->request($this->client);

                $this->logger->info('Article attachment created successfully');
                $this->logger->debug(json_encode($response->article_attachment));

            } catch (ResponseException $e) {
                $this->handleResponseException('article attachment');
            }
        }

        $locales = array_keys(LocaleMapper::getLocaleCodesMapping());

        foreach ($locales as $locale) {
            try {
                $helper = new ArticleTranslationCreate(array(
                    'id'          => $article->id,
                    'translation' => array(
                        'locale' => $locale,
                        'title'  => 'Translation title ' . $locale,
                        'body'   => 'Translation body ' . $locale,
                    ),
                ));

                $response = $helper->request($this->client);

                $this->logger->info('Article translation created successfully');
                $this->logger->debug(json_encode($response->translation));

            } catch (ResponseException $e) {
                $this->handleResponseException('article translation');
            }
        }
    }
}
