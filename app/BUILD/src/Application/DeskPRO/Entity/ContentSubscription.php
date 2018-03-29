<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * A single table that controls subscriptions to all common content types.
 */
class ContentSubscription extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * Enable email notifications for the subscription.
     *
     * @var bool
     */
    protected $use_email = false;

    /**
     * The last time the user dismissed a notice about this sub.
     *
     * @var \DateTime
     */
    protected $last_dismiss_date;

    /**
     * The last time we emailed the user about this sub.
     *
     * @var \DateTime
     */
    protected $last_email_date;

    /**
     * The last time the subscription was updated.
     *
     * @var \DateTime
     */
    protected $updated_date;

    /**
     * @var \Application\DeskPRO\Entity\Article
     */
    protected $article = null;

    /**
     * @var \Application\DeskPRO\Entity\Download
     */
    protected $download = null;

    /**
     * @var \Application\DeskPRO\Entity\Feedback
     */
    protected $feedback = null;

    /**
     * @var \Application\DeskPRO\Entity\News
     */
    protected $news = null;

    /**
     * @param $content_object
     * @param $person
     *
     * @return \Application\DeskPRO\Entity\ContentSubscription
     */
    public static function create($content_object, $person)
    {
        $sub         = new self();
        $sub->person = $person;

        if ($content_object instanceof Article) {
            $sub->article = $content_object;
        } elseif ($content_object instanceof Download) {
            $sub->download = $content_object;
        } elseif ($content_object instanceof News) {
            $sub->news = $content_object;
        } elseif ($content_object instanceof Feedback) {
            $sub->feedback = $content_object;
        } else {
            throw new \InvalidArgumentException('$content_object must be Article, Download, News or Feedback. Got `'.get_class($content_object).'`');
        }

        return $sub;
    }

    public function __construct()
    {
        $this['last_dismiss_date'] = new \DateTime();
        $this['last_email_date']   = new \DateTime();
        $this['updated_date']      = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * "touch"es this subscription to update the last_X_date's, so whatever notifications
     * are involved are reset.
     */
    public function touch()
    {
        $this['last_dismiss_date'] = new \DateTime();
        $this['last_email_date']   = new \DateTime();
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ContentSubscription';
        $metadata->setPrimaryTable(['name' => 'content_subscriptions']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField(['fieldName' => 'id', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'id', 'id' => true]);
        $metadata->mapField(['fieldName' => 'use_email', 'type' => 'boolean', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'use_email']);
        $metadata->mapField(['fieldName' => 'last_dismiss_date', 'type' => 'datetime', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'last_dismiss_date']);
        $metadata->mapField(['fieldName' => 'last_email_date', 'type' => 'datetime', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'last_email_date']);
        $metadata->mapField(['fieldName' => 'updated_date', 'type' => 'datetime', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'updated_date']);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(['fieldName' => 'person', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Person', 'mappedBy' => null, 'inversedBy' => null, 'joinColumns' => [0 => ['name' => 'person_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'cascade', 'columnDefinition' => null]]]);
        $metadata->mapManyToOne(['fieldName' => 'article', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Article', 'mappedBy' => null, 'inversedBy' => null, 'joinColumns' => [0 => ['name' => 'article_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'cascade', 'columnDefinition' => null]]]);
        $metadata->mapManyToOne(['fieldName' => 'download', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Download', 'mappedBy' => null, 'inversedBy' => null, 'joinColumns' => [0 => ['name' => 'download_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'cascade', 'columnDefinition' => null]]]);
        $metadata->mapManyToOne(['fieldName' => 'feedback', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Feedback', 'mappedBy' => null, 'inversedBy' => null, 'joinColumns' => [0 => ['name' => 'feedback_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'cascade', 'columnDefinition' => null]]]);
        $metadata->mapManyToOne(['fieldName' => 'news', 'targetEntity' => 'Application\\DeskPRO\\Entity\\News', 'mappedBy' => null, 'inversedBy' => null, 'joinColumns' => [0 => ['name' => 'news_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'cascade', 'columnDefinition' => null]]]);
    }
}
