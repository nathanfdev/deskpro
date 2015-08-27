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
use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Generator\Exporter\Formatter\FormatterInterface;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerException;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\ParserHelperSet;
use Application\ImportBundle\Generator\Exporter\Parser\SkippingException;
use Application\ImportBundle\Reader\ZenDesk\LocaleMapper;
use Application\ImportBundle\Reader\ZenDesk\ZenDeskReaderInterface;

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
     * Constructor
     *
     * @param ZenDeskReaderInterface       $reader
     * @param FormatterInterface           $formatter
     * @param ParserHelperSet              $helpers
     * @param ParserPeopleStorageInterface $people_storage
     */
    public function __construct(
        ZenDeskReaderInterface       $reader,
        FormatterInterface           $formatter,
        ParserHelperSet              $helpers,
        ParserPeopleStorageInterface $people_storage
    ) {
        parent::__construct($reader, $formatter, $helpers);
        $this->article_people = $people_storage;
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
        return count($this->getArticles(true));
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $articles   = $this->getArticles();

        $collection = new Entity\Collection();
        $collection->setExpectedCount(count($articles));

        foreach ($articles as $num => $data) {
            $this->advanceProgressBar();

            try {
                $entity = $this->exportArticle($data);

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));

            } catch (SkippingException $e) {
                $this->logSkippingException('ZDArticle', $this->getEntityType(), 'id', $e);
            } catch (TransformerException $e) {
                $this->logTransformerException('ZDArticle', $this->getEntityType(), 'id', $e);
            } catch (\Exception $e) {
                $this->logUnknownException('ZDArticle', $this->getEntityType(), 'id', $e, $data);
            }
        }

        return $collection;
    }

    /**
     * Returns a article entity
     *
     * @param array $data
     *
     * @return Entity\Article
     * @throws SkippingException
     */
    private function exportArticle(array $data)
    {
        $formatted = $this->formatter->format($data, array(
            'id'           => TransformerInterface::TYPE_INT,
            'destination'  => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => 'article_',
                'ref'    => 'id',
            )),
            'author_id'    => TransformerInterface::TYPE_INT,
            'section_id'   => TransformerInterface::TYPE_INT,
            'title'        => TransformerInterface::TYPE_STRING,
            'body'         => TransformerInterface::TYPE_STRING,
            'created_at'   => TransformerInterface::TYPE_DATE,
            'updated_at'   => TransformerInterface::TYPE_DATE,
            'vote_sum'     => TransformerInterface::TYPE_INT,
            'vote_count'   => TransformerInterface::TYPE_INT,
            'locale'       => TransformerInterface::TYPE_STRING,
            'draft'        => TransformerInterface::TYPE_BOOLEAN,
            'label_names'  => TransformerInterface::TYPE_ARRAY,
            'comments'     => TransformerInterface::TYPE_ARRAY,
            'attachments'  => TransformerInterface::TYPE_ARRAY,
            'translations' => TransformerInterface::TYPE_ARRAY,
        ));

        if (empty($formatted['author_id'])) {
            throw new SkippingException('Article without author_id, skipping', $formatted);
        }

        $author_email = $this->article_people->getPersonEmail($formatted['author_id']);
        if ( ! $author_email) {
            throw new SkippingException('Unable to get article author, skipping', $formatted);
        }

        $entity = new Entity\Article();
        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setImportMapKey(DeskPROEntity\ImportMap::TYPE_ZENDESK_ARTICLE)
            ->setOid($formatted['id'])
            ->setPersonEmail($author_email)
            ->setTitle($formatted['title'])
            ->setContent($formatted['body'])
            ->setLanguage(LocaleMapper::getLocale($formatted['locale']))
            ->setDateCreated($formatted['created_at'])
            ->setDateUpdated($formatted['updated_at'])
            ->setNumComments($formatted['vote_count'])
            ->setNumRatings($formatted['vote_sum'])
        ;

        if ($formatted['draft']) {
            $entity->setStatus(DeskPROEntity\Article::STATUS_HIDDEN . '.' . DeskPROEntity\Article::HIDDEN_STATUS_DRAFT);
        } else {
            $entity->setStatus(DeskPROEntity\Article::STATUS_PUBLISHED);
        }
        if ($formatted['section_id']) {
            $section = $this->reader->getArticleCategory($formatted['section_id']);
            if (isset($section['name'])) {
                $entity->addCategory($section['name']);
            }
        }

        foreach ($formatted['label_names'] as $label) {
            $entity->addLabel($label);
        }

        $comments = $this->exportComments($formatted['comments']);
        foreach ($comments as $comment) {
            $entity->addComment($comment);
        }

        $attachments = $this->getAttachmentParser()->export($formatted['attachments']);
        foreach ($attachments as $attachment) {
            $entity->addAttachment($attachment);
        }

        $translations = $this->getTranslationsParser()->export($formatted['translations']);
        foreach ($translations as $translation) {
            $entity->addTranslation($translation);
        }

        return $entity;
    }

    /**
     * Returns a collection of the article comments
     *
     * @param array $comments
     * @return Entity\ArticleComment[]
     */
    private function exportComments(array $comments)
    {
        $collection = new Entity\Collection();

        foreach ($comments as $num => $data) {
            try {
                $entity = $this->exportComment($data);

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));

            } catch (SkippingException $e) {
                $this->logSkippingException('ZDArticleComment', 'article comment', 'id', $e);
            } catch (TransformerException $e) {
                $this->logTransformerException('ZDArticleComment', 'article comment', 'id', $e);
            } catch (\Exception $e) {
                $this->logUnknownException('ZDArticleComment', 'article comment', 'id', $e, $data);
            }
        }

        return $collection;
    }

    /**
     * Returns a comment entity
     *
     * @param array $data
     * @return Entity\ArticleComment
     */
    private function exportComment(array $data)
    {
        $formatted = $this->formatter->format($data, array(
            'id'          => TransformerInterface::TYPE_INT,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => 'article_comment_',
                'ref'    => 'id',
            )),
            'body'        => TransformerInterface::TYPE_STRING,
            'author_id'   => TransformerInterface::TYPE_INT,
            'created_at'  => TransformerInterface::TYPE_DATE,
        ));

        if (empty($formatted['author_id'])) {
            throw new SkippingException('Article comment without author_id, skipping', $formatted);
        }

        $author_email = $this->article_people->getPersonEmail($formatted['author_id']);
        if ( ! $author_email) {
            throw new SkippingException('Unable to get article comment author, skipping', $formatted);
        }

        $entity = new Entity\ArticleComment();
        $entity
            ->setRawData($data)
            ->setOid($formatted['id'])
            ->setDestination($formatted['destination'])
            ->setPersonEmail($author_email)
            ->setContent($formatted['body'])
            ->setDateCreated($formatted['created_at'])
            ->setAsReviewed(true)
            ->setStatus(DeskPROEntity\ArticleComment::STATUS_VISIBLE)
        ;

        return $entity;
    }

    /**
     * Returns articles
     * Loads data from ZenDesk reader
     *
     * @param boolean $count_only
     *
     * @return array
     * @throws \Exception
     */
    private function getArticles($count_only = false)
    {
        $this->logDebugTimeStart('getArticles', "Reading articles batch");

        $articles = array();
        $start_time = $this->getBatchConfig()->getArticlesEndTime();

        if ($start_time < new \DateTime('-5 minutes')) {
            if ($start_time) {
                $this->logDebug(sprintf("Reading from time: %s", $start_time->format('Y-m-d H:i:s')));
            } else {
                $this->logDebug(sprintf("Reading from time: %s", "Beginning"));
            }

            $response = $this->reader->getArticles($start_time);

            if (count($response)) {
                // ZenDesk API does not allow to get article comments in a single request due to huge response (could be up to ~20 MB)
                // We have to load comments for each article separately
                foreach ($response as $article) {
                    if ( ! $count_only) {
                        $this->logDebug(sprintf('[ZDArticle #%s] Reading comments', $article['id']));
                        $article['comments'] = $this->reader->getArticleComments($article['id']);

                        $this->logDebug(sprintf('[ZDArticle #%s] Reading attachments', $article['id']));
                        $article['attachments'] = $this->reader->getArticleAttachments($article['id']);

                        $this->logDebug(sprintf('[ZDArticle #%s] Reading translations', $article['id']));
                        $article['translations'] = $this->reader->getArticleTranslations($article['id']);
                    }

                    $articles[] = $article;
                }

                $this->article_people->loadBy($articles);

                $this->end_time = $this->reader->getArticlesEndTime($start_time);
                if ($this->end_time == $start_time) {
                    $this->end_time->modify('+1 second');
                }

                $this->logDebug(sprintf("New end time: %s", $this->end_time->format('Y-m-d H:i:s')));

            } else {
                $this->logDebug(sprintf("No more records"));
            }

        } else {
            $this->logAlert('No article was exported due 5 minutes timeout of the last end time');
        }

        $this->logDebug(sprintf('Read %d article', count($articles)));
        $this->logDebugTimeEnd('getArticles', 'Done reading articles batch');

        return $articles;
    }
}
