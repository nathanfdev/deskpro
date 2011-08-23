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
class Download extends ContentAbstract
{
	/**
	 * @var \Application\DeskPRO\Entity\TicketCategory
	 * @ORM_Mapping\ManyToOne(targetEntity="DownloadCategory", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="category_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $category;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="DownloadRevision", mappedBy="download", cascade={"persist", "remove", "merge"}, indexBy="id")
	 */
	protected $revisions;

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
	 * @ORM_Mapping\OneToMany(targetEntity="LabelDownload", mappedBy="download", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $labels;

	/**
	 * @var \Application\DeskPRO\Labels\LabelManager
	 */
	protected $_label_manager = null;

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
}
