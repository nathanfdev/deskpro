<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;

use Application\DeskPRO\App;

use Orb\Util\Strings;

/**
 * A download/file available from the protal
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\Download")
  * @ORM_Mapping\Table(name="downloads", indexes={
 *     @ORM_Mapping\Index(name="date_published_idx", columns={"date_published"})
 * })
 * @ORM_Mapping\HasLifecycleCallbacks
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
		if (!$this->blob) {
			return '';
		}
		return $this->blob['filename'];
	}

	public function getFileSize()
	{
		if (!$this->blob) {
			return 0;
		}
		return $this->blob['filesize'];
	}

	public function getReadableFileSize()
	{
		if (!$this->blob) {
			return '0 B';
		}
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

	/**
	 * @ORM_Mapping\PostUpdate
	 * @ORM_Mapping\PostPersist
	 */
	public function _updateSearchIndex() { $this->_queueSearchIndexUpdate(); }
	/**
	 * @ORM_Mapping\PostRemove
	 */
	public function _deleteSearchIndex() { $this->_queueSearchIndexUpdate('delete'); }

	public function _queueSearchIndexUpdate($op = 'update')
	{
		$container = App::getContainer();
		if ($container instanceof \Application\DeskPRO\DependencyInjection\DeskproContainer) {
			$container->getSystemService('search_indexer')->update($this, $op);
		}
	}
}
