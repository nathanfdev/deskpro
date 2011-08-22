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

use Doctrine\ORM\Mapping as ORM_Mapping;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;
use \Application\DeskPRO\Markdown;

use \Orb\Util\Strings;

/**
 * A download/file available from the protal
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\Download")
 * @ORM_Mapping\Table(name="downloads")
 */
class Download extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\TicketCategory
	 * @ORM_Mapping\ManyToOne(targetEntity="DownloadCategory", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="category_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $category;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="slug", type="string", length=100)
	 */
	protected $slug;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="content", type="text")
	 */
	protected $content;

	/**
	 * @var \Application\DeskPRO\Entity\Blob
	 * @ORM_Mapping\ManyToOne(targetEntity="Blob", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="blob_id", referencedColumnName="id")
	 */
	protected $blob;

	/**
	 * Total number of downloads
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="num_downloads", type="integer")
	 */
	protected $num_downloads = 0;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @ORM_Mapping\OneToMany(targetEntity="LabelDownload", mappedBy="download", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $labels;

	/**
	 * @var \Application\DeskPRO\Labels\LabelManager
	 */
	protected $_label_manager = null;

	public function __construct()
	{
		$this->date_created = new \DateTime();
		$this->comments = new \Doctrine\Common\Collections\ArrayCollection();
		$this->labels = new \Doctrine\Common\Collections\ArrayCollection();
	}

	public function getFileName()
	{
		return $this->blob['filename'];
	}

	public function getFileSize()
	{
		return $this->blob['filesize'];
	}

	public function getReadableFileSize()
	{
		return $this->blob->getReadableFilesize();
	}

	public function setTitle($title)
	{
		$this->setModelField('title', $title);

		if (!$this->slug) {
			$this['slug']  = Strings::slugifyTitle($title);
		}
	}

	public function getContentHtml()
	{
		return Markdown::format($this->content);
	}

	public function getUrlSlug()
	{
		return $this->id . '-' . $this->slug;
	}

	public function getLink()
	{
		$url = App::getRouter()->generate('user_downloads_file', array('slug' => $this->getUrlSlug()), true);

		return $url;
	}

	public function getPermalink()
	{
		$url = App::getRouter()->generate('user_downloads_file', array('slug' => $this->id), true);

		return $url;
	}

	public function getCategoryPath()
	{
		$path = array();

		$cat = $this->category;
		$path[] = $cat;
		while ($cat['parent']) {
			$cat = $cat['parent'];
			$path[] = $cat;
		}

		return $path;
	}

	/**
	 * Add a label
	 * @param \Application\DeskPRO\Entity\LabelDownload $label
	 */
	public function addLabel(LabelDownload $label)
	{
		$label['download'] = $this;
		$this->labels->add($label);
	}

	/**
	 * @return \Application\DeskPRO\Labels\LabelManager
	 */
	public function getLabelManager()
	{
		if ($this->_label_manager === null) {
			$this->_label_manager = new \Application\DeskPRO\Labels\LabelManager($this, 'DeskPRO:LabelDownload');
		}

		return $this->_label_manager;
	}
}
