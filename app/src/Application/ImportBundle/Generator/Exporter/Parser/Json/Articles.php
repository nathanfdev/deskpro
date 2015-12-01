<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\ImportBundle\Generator\Exporter\Parser\Json;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;
use Application\ImportBundle\Reader\Json\JsonReaderInterface;

/**
 * Articles json file parser.
 *
 * Class Articles
 */
final class Articles extends AbstractParser
{
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
        return $this->reader->getDirectoryFilesCount(JsonReaderInterface::ENTITY_ARTICLE_PATH, $this->getBatchNum());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($this->reader->getData(JsonReaderInterface::ENTITY_ARTICLE_PATH, $this->getBatchNum()))
            ->setPrefix('JSONArticle')
            ->setRefColumn('oid')
            ->setMethod('exportArticle')
            ->setAdvanceProgressbar(true)
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns an article entity.
     *
     * @param array $data
     *
     * @return Entity\Article|null
     */
    protected function exportArticle(array $data)
    {
        $formatted = $this->formatter->format($data, array(
            'oid'            => TransformerInterface::TYPE_STRING,
            'import_map_key' => TransformerInterface::TYPE_STRING,
            'destination'    => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => 'article_',
                'ref'    => 'oid',
            )),
            'person'         => TransformerInterface::TYPE_STRING,
            'title'          => TransformerInterface::TYPE_STRING,
            'content'        => TransformerInterface::TYPE_STRING,
            'slug'           => TransformerInterface::TYPE_STRING,
            'language'       => TransformerInterface::TYPE_STRING,
            'end_action'     => TransformerInterface::TYPE_STRING,
            'total_rating'   => TransformerInterface::TYPE_STRING,
            'num_comments'   => TransformerInterface::TYPE_INT,
            'num_ratings'    => TransformerInterface::TYPE_INT,
            'view_count'     => TransformerInterface::TYPE_INT,
            'status'         => TransformerInterface::TYPE_STRING,
            'date_created'   => TransformerInterface::TYPE_DATE,
            'date_published' => TransformerConfiguration::create(TransformerInterface::TYPE_DATE, array(
                'null' => true,
            )),
            'date_updated' => TransformerConfiguration::create(TransformerInterface::TYPE_DATE, array(
                'null' => true,
            )),
            'date_end' => TransformerConfiguration::create(TransformerInterface::TYPE_DATE, array(
                'null' => true,
            )),
            'categories'    => TransformerInterface::TYPE_ARRAY,
            'labels'        => TransformerInterface::TYPE_ARRAY,
            'custom_fields' => TransformerInterface::TYPE_ARRAY,
            'comments'      => TransformerInterface::TYPE_ARRAY,
            'attachments'   => TransformerInterface::TYPE_ARRAY,
            'translations'  => TransformerInterface::TYPE_ARRAY,
        ));

        $entity = new Entity\Article();
        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setImportMapKey($formatted['import_map_key'])
            ->setOid($formatted['oid'])
            ->setPersonEmail($formatted['person'])
            ->setTitle($formatted['title'])
            ->setContent($formatted['content'])
            ->setSlug($formatted['slug'])
            ->setLanguage($formatted['language'])
            ->setEndAction($formatted['end_action'])
            ->setViewCount($formatted['view_count'])
            ->setTotalRating($formatted['total_rating'])
            ->setNumComments($formatted['num_comments'])
            ->setNumRatings($formatted['num_ratings'])
            ->setStatus($formatted['status'])
            ->setDateCreated($formatted['date_created'])
            ->setDateUpdated($formatted['date_updated'])
            ->setDatePublished($formatted['date_published'])
            ->setDateEnd($formatted['date_end'])
        ;

        foreach ($formatted['categories'] as $category) {
            $entity->addCategory($category);
        }
        foreach ($formatted['labels'] as $label) {
            $entity->addLabel($label);
        }

        $custom_fields = $this->getCustomFieldsParser()->export($formatted['custom_fields']);
        foreach ($custom_fields as $custom_field) {
            $entity->addCustomField($custom_field);
        }

        $attachments = $this->getAttachmentParser()->exportAttachments($formatted['attachments']);
        foreach ($attachments as $attachment) {
            $entity->addAttachment($attachment);
        }

        $comments = $this->exportComments($formatted['comments']);
        foreach ($comments as $comment) {
            $entity->addComment($comment);
        }

        $translations = $this->getTranslationsParser()->export($formatted['translations']);
        foreach ($translations as $translation) {
            $entity->addTranslation($translation);
        }

        return $entity;
    }

    /**
     * Returns a collection of article comment messages.
     *
     * @param array $comments
     *
     * @return Entity\ArticleComment[]
     */
    private function exportComments(array $comments)
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($comments)
            ->setPrefix('JSONArticleComment')
            ->setRefColumn('oid')
            ->setMethod('exportComment')
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns an article comment entity.
     *
     * @param array $data
     *
     * @return Entity\ArticleComment
     */
    protected function exportComment(array $data)
    {
        $formatted = $this->formatter->format($data, array(
            'oid'         => TransformerInterface::TYPE_STRING,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => 'article_comment_',
                'ref'    => 'oid',
            )),
            'person_email' => TransformerInterface::TYPE_STRING,
            'content'      => TransformerInterface::TYPE_STRING,
            'status'       => TransformerInterface::TYPE_STRING,
            'is_reviewed'  => TransformerInterface::TYPE_BOOLEAN,
            'validating'   => TransformerInterface::TYPE_STRING,
            'date_created' => TransformerInterface::TYPE_DATE,
        ));

        $entity = new Entity\ArticleComment();
        $entity
            ->setRawData($data)
            ->setOid($formatted['oid'])
            ->setDestination($formatted['destination'])
            ->setPersonEmail($formatted['person_email'])
            ->setContent($formatted['content'])
            ->setStatus($formatted['status'])
            ->setAsReviewed($formatted['is_reviewed'])
            ->setDateCreated($formatted['date_created'])
        ;

        return $entity;
    }
}
