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
use Orb\Util\Arrays;

/**
 * News
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\News")
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Table(name="news", indexes={
 *     @ORM_Mapping\Index(name="date_published_idx", columns={"date_published"})
 * })
 */
class News extends ContentAbstract
{
	/**
	 * @var \Application\DeskPRO\Entity\NewsCategory
	 * @ORM_Mapping\ManyToOne(targetEntity="NewsCategory", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="category_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $category;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="NewsRevision", mappedBy="news", cascade={"persist", "remove", "merge"}, indexBy="id")
	 */
	protected $revisions;

	/**
	 * @ORM_Mapping\OneToMany(targetEntity="LabelNews", mappedBy="news", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $labels;

	public function getContentHtml()
	{
		$content = $this->content;

		// Remove the intro separator
		$content = preg_replace('#[\r\n]+\-{3,}[\r\n]+#', "\n", $content);

		return $content;
	}

	public function getExcerptHtml()
	{
		$content = Strings::standardEol($this->content);
		if ($pos = strpos($content, '![more]')) {
			$excerpt = substr($content, $pos);
		} elseif ($pos = strpos($content, "\n\n")) {
			$excerpt = substr($content, 0, $pos);
		} else {
			$excerpt = $content;
		}

		if (str_word_count($excerpt) > 50) {
			$words = str_word_count($excerpt, 2);
			$pos = Arrays::getNthKey($words, 50);
			$excerpt = substr($excerpt, 0, $pos);
			$excerpt = preg_replace('#[^a-zA-Z0-9]$#', '', $excerpt);
			$excerpt .= '...';
		}

		return $excerpt;
	}

	public function getCountWordsAfterExcerpt()
	{
		$content = strip_tags($this->getContentHtml());
		$exceprt = strip_tags($this->getExcerptHtml());

		$diff = str_word_count($content) - str_word_count($exceprt);

		return $diff;
	}

	public function getLink()
	{
		$url = App::getRouter()->generate('user_news_view', array('slug' => $this->getUrlSlug()), true);

		return $url;
	}

	public function getPermalink()
	{
		$url = App::getRouter()->generate('user_news_view', array('slug' => $this->id), true);

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

	public function addLabel(LabelNews $label)
	{
		$label['news'] = $this;
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
