<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2011 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Ricardo Rauch <ricardo@gravityonmars.com>
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;

/**
 * TaskComment entity definition
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="task_comments")
 */
class TaskComment extends \Application\DeskPRO\Domain\DomainObject
{

	/**
	 * The unique ID.
	 *
	 * @var int
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\generatedValue(strategy="IDENTITY")
	 * @ORM_Mapping\Column(name="id", type="integer")
	 * 
	 */
	protected $id = null;

	/**
	 * The comment's content
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="content", type="text")
	 */
	protected $content = '';

	/**
	 * @var Application\DeskPRO\Entity\Task
	 * @ORM_Mapping\ManyToOne(
	 * 	targetEntity="Task",
	 * 	inversedBy="comments",
	 * 	cascade={"persist", "remove", "merge"}
	 * )
	 * @ORM_Mapping\JoinColumn(name="task_id", referencedColumnName="id", nullable=false, onDelete="cascade")
	 */
	protected $task;
 	
	/**
	 * @var Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(
	 * 	targetEntity="Person",
	 * 	inversedBy="task_comments",
	 * 	cascade={"persist", "remove", "merge"}
	 * )
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person;
 	
	/**
	 * The date the comment was inserted into the system
	 *
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;


	
	/**
	 * Creates a new comment with the provided content.
	 * 
	 * @param \Application\DeskPRO\Entity\Person $creator The comment's creator.
	 * @param string $content The comment's content
	 */
	public function __construct(Person $creator, $content)
	{
		$this->person = $creator;
		$this->content = $content;

		$this->date_created = new \DateTime();
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
	 * @param int id The person's id.
	 * @throws \InvalidArgumentException Thrown when there's no preson with the
	 *                                   id is not in the databse.
	 */
	public function setPersonId($id)
	{
		if ($this->person['id'] == $id) {
			return;
		}
		
		$person = App::getEntityRepository('DeskPRO:Person')->find($id);
		
		if (! $person) {
			throw new \InvalidArgumentException('No person for id ' . $id);
		}
		
		$this->person->taskComments->remove($this);
		$this->person = $person;
	}



}

