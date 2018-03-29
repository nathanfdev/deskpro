<?php

namespace Application\DeskPRO\Entity;

use DateTime;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;

class TopicRevision extends RevisionAbstract
{
    /**
     * @JMS\Type("entity<Application\DeskPRO\Entity\Topic>")
     * @JMS\Groups({"list", "details"})
     *
     * @var Topic
     */
    protected $topic;

    /**
     * Revision title of topic.
     *
     * @var string
     */
    protected $title = '';

    /**
     * Revision content of topic.
     *
     * @var string
     */
    protected $content = '';

    /**
     * @return Topic
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * @param Topic $topic
     *
     * @return TopicRevision
     */
    public function setTopic($topic)
    {
        $this->setModelField('topic', $topic);

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return TopicRevision
     */
    public function setTitle($title)
    {
        $this->setModelField('title', $title);

        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @return TopicRevision
     */
    public function setContent($content)
    {
        $this->setModelField('content', $content);

        return $this;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Person $person
     *
     * @return TopicRevision
     */
    public function setPerson($person)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return TopicRevision
     */
    public function setStatus($status)
    {
        $this->setModelField('status', $status);

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param DateTime $date_created
     *
     * @return TopicRevision
     */
    public function setDateCreated($date_created)
    {
        $this->setModelField('date_created', $date_created);

        return $this;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(['name' => 'topic_revisions']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'title',
            'type'       => 'string',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'title',
        ]);
        $metadata->mapField([
            'fieldName'  => 'content',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'content',
        ]);
        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'status',
            'type'       => 'string',
            'length'     => 30,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'status',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_created',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'topic',
            'targetEntity' => Topic::class,
            'mappedBy'     => null,
            'inversedBy'   => 'revisions',
            'joinColumns'  => [
                [
                    'name'                 => 'topic_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'person',
            'targetEntity' => Person::class,
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                [
                    'name'                 => 'person_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
    }
}
