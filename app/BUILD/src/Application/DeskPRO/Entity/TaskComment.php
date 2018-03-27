<?php

/**
 * DeskPRO.
 *
 * @category Entities
 *
 * @copyright Copyright (c) 2011 DeskPRO (http://www.deskpro.com/)
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Markdown;
use DeskPRO\Component\Util\RegexUtils;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;
use Orb\Util\Strings;

/**
 * TaskComment entity definition.
 */
class TaskComment extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * The unique ID.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id = null;

    /**
     * The comment's content.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $content = '';

    /**
     * Task containing comment.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Task>")
     *
     * @var Task
     */
    protected $task;

    /**
     * Person created this comment.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    protected $person;

    /**
     * The date the comment was inserted into the system.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $date_created;

    /**
     * Creates a new comment with the provided content.
     *
     * @param Person $creator The comment's creator
     * @param string $content The comment's content
     */
    public function __construct(Person $creator = null, $content = '')
    {
        $this->person  = $creator;
        $this->content = $content;

        $this->date_created = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return $this
     */
    public function setContent($content)
    {
        $this->setModelField('content', $content);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * @param Task $task
     *
     * @return $this
     */
    public function setTask(Task $task)
    {
        $this->setModelField('task', $task);

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
     * @return $this
     */
    public function setPerson($person)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param \DateTime $dateCreated
     *
     * @return $this
     */
    public function setDateCreated(\DateTime $dateCreated)
    {
        $this->setModelField('date_created', $dateCreated);

        return $this;
    }

    /**
     * Returns the creator's id.
     *
     * @return int
     */
    public function getPersonId()
    {
        return $this->person['id'];
    }

    /**
     * Sets the task comment's creator id.
     *
     * @param int $id The person's id
     *
     * @throws \InvalidArgumentException Thrown when there's no preson with the
     *                                   id is not in the databse
     */
    public function setPersonId($id)
    {
        if ($this->person['id'] == $id) {
            return;
        }

        $person = App::getEntityRepository('DeskPRO:Person')->find($id);

        if (!$person) {
            throw new \InvalidArgumentException('No person for id '.$id);
        }

        $this->person->taskComments->remove($this);
        $this->setModelField('person', $person);
    }

    public function getContentHtml()
    {
        return Markdown::format(htmlspecialchars($this->content, \ENT_NOQUOTES, 'UTF-8'));
    }

    public function getContentHtmlPlain()
    {
        return nl2br(htmlspecialchars($this->content));
    }

    public function getContentPlain()
    {
        if (!$this->content) {
            return '';
        }
        $content = Strings::standardEol($this->content);
        $content = RegexUtils::safePregReplace("#<br\s*/?><p>#", '<p>', $content);
        $content = RegexUtils::safePregReplace("#<p></p><br\s*/?>#", '<p>', $content);
        $content = RegexUtils::safePregReplace("#</p><br\s*/?>#", '</p>', $content);
        $content = RegexUtils::safePregReplace("#<br\s*/?></p>#", '</p>', $content);
        $content = RegexUtils::safePregReplace("#<br\s*/?>?#", "\n", $content);
        $content = RegexUtils::safePregReplace("#<p>\n?#", "\n", $content);
        $content = RegexUtils::safePregReplace("#\n?</p>#", "\n", $content);
        $content = html_entity_decode(strip_tags($content), \ENT_QUOTES, 'UTF-8');
        $content = trim($content);

        $lines_raw = explode("\n", $content);
        $lines     = [];
        foreach ($lines_raw as $l) {
            $lines[] = trim($l);
        }

        $content = implode("\n", $lines);
        $content = RegexUtils::safePregReplace("#\n{3,}#", "\n\n", $content);

        return $content;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Basic';
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(['name' => 'task_comments']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
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
            'fieldName'  => 'content',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'content',
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
            'fieldName'    => 'task',
            'targetEntity' => Task::class,
            'cascade'      => [
                0 => 'remove',
                1 => 'persist',
                3 => 'merge',
            ],
            'mappedBy'    => null,
            'inversedBy'  => 'comments',
            'joinColumns' => [
                0 => [
                    'name'                 => 'task_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => false,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'person',
            'targetEntity' => Person::class,
            'mappedBy'     => null,
            'inversedBy'   => 'task_comments',
            'joinColumns'  => [
                0 => [
                    'name'                 => 'person_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                    'columnDefinition'     => null,
                ],
            ],
            'dpApi' => true,
        ]);
    }
}
