<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Entity;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;
use \Application\DeskPRO\Markdown;

use \Orb\Util\Strings;

/**
 * A download/file available from the protal
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\Download")
 * @orm:Table(name="downloads")
 */
class Download extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\TicketCategory
	 * @orm:ManyToOne(targetEntity="DownloadCategory", fetch="EAGER")
	 * @orm:JoinColumn(name="category_id", referencedColumnName="id")
	 */
	protected $category;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person = null;

	/**
	 * @var string
	 * @orm:Column(name="slug", type="string", length=100)
	 */
	protected $slug;

	/**
	 * @var string
	 * @orm:Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * @var string
	 * @orm:Column(name="content", type="text")
	 */
	protected $content;

	/**
	 * @var \Application\DeskPRO\Entity\Blob
	 * @orm:ManyToOne(targetEntity="Blob", fetch="EAGER")
	 * @orm:JoinColumn(name="blob_id", referencedColumnName="id")
	 */
	protected $blob;

	/**
	 * Total number of downloads
	 *
	 * @var string
	 * @orm:Column(name="num_downloads", type="integer")
	 */
	protected $num_downloads = 0;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @orm:OneToMany(targetEntity="LabelDownload", mappedBy="download", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $labels;

	/**
	 * @var \Application\DeskPRO\Labels\LabelManager
	 */
	protected $_label_manager = null;

	public function __consturct()
	{
		$this->date_created = new \DateTime();
		$this->comments = new \Doctrine\Common\Collections\ArrayCollection();
		$this->labels = new \Doctrine\Common\Collections\ArrayCollection();
	}

	public function getContentHtml()
	{
		return Markdown::format($this->content);
	}

	public function getUrlSlug()
	{
		return $this->id . '-' . $this->slug;
	}

	/**
	 * @return \Application\DeskPRO\Labels\LabelManager
	 */
	public function getLabelManager()
	{
		if ($this->_label_manager === null) {
			$this->_label_manager = new \Application\DeskPRO\Labels\LabelManager($this, 'DeskPRO:LabelArticle');
		}

		return $this->_label_manager;
	}
}