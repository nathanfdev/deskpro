<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
use Orb\Util\Strings;

/**
 * TaskComment entity definition.
 *
 * SWG\Model
 */
class TaskComment extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * The unique ID.
     *
     * @var int
     *          SWG\Property(name="id",type="integer")
     */
    protected $id = null;

    /**
     * The comment's content.
     *
     * @var string
     *             SWG\Property(name="content",type="string")
     */
    protected $content = '';

    /**
     * @var Application\DeskPRO\Entity\Task
     *                                      targetEntity="Task",
     *                                      inversedBy="comments",
     *                                      cascade={"persist", "remove", "merge"}
     *                                      )
     *                                      SWG\Property(name="task", type="Task")
     */
    protected $task;

    /**
     * @var Application\DeskPRO\Entity\Person
     *                                        targetEntity="Person",
     *                                        inversedBy="task_comments",
     *                                        cascade={"persist", "remove", "merge"}
     *                                        )
     *                                        SWG\Property(name="person",type="Person")
     */
    protected $person;

    /**
     * The date the comment was inserted into the system.
     *
     * @var \DateTime
     *                SWG\Property(name="date_created",type="integer")
     */
    protected $date_created;

    /**
     * Creates a new comment with the provided content.
     *
     * @param \Application\DeskPRO\Entity\Person $creator The comment's creator
     * @param string                             $content The comment's content
     */
    public function __construct(Person $creator, $content)
    {
        $this['person']  = $creator;
        $this['content'] = $content;

        $this['date_created'] = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @param int id The person's id
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
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Task',
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
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
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
