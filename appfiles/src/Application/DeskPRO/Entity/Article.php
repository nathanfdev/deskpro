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
 * Article
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\Article")
 * @ORM_Mapping\Table(name="articles")
 */
class Article extends ContentAbstract
{
	const END_ACTION_DELETE  = 'delete';
	const END_ACTION_ARCHIVE = 'archive';

	/**
	 * @var Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\ManyToMany(targetEntity="ArticleCategory", cascade={"persist", "remove", "merge"}, indexBy="id")
     * @ORM_Mapping\JoinTable(name="article_to_categories", joinColumns={@ORM_Mapping\JoinColumn(name="article_id", referencedColumnName="id", onDelete="cascade")}, inverseJoinColumns={@ORM_Mapping\JoinColumn(name="category_id", referencedColumnName="id", onDelete="cascade")})
	 */
	protected $categories;

	/**
	 * @var Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\ManyToMany(targetEntity="Product", cascade={"persist", "remove", "merge"}, indexBy="id")
     * @ORM_Mapping\JoinTable(name="article_to_product", joinColumns={@ORM_Mapping\JoinColumn(name="article_id", referencedColumnName="id", onDelete="cascade")}, inverseJoinColumns={@ORM_Mapping\JoinColumn(name="product_id", referencedColumnName="id", onDelete="cascade")})
	 */
	protected $products;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="ArticleRevision", mappedBy="article", cascade={"persist", "remove", "merge"}, indexBy="id")
	 */
	protected $revisions;

	/**
	 * @ORM_Mapping\OneToMany(targetEntity="ArticleAttachment", mappedBy="article", cascade={"persist", "remove", "merge"}, indexBy="id")
	 */
	protected $attachments;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_end",type="datetime", nullable=true)
	 */
	protected $date_end;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="end_action", type="string", length=10, nullable=true)
	 */
	protected $end_action = null;

	/**
	 * @ORM_Mapping\OneToMany(targetEntity="LabelArticle", mappedBy="article", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $labels;

	public function __construct()
	{
		parent::__construct();

		$this->products    = new \Doctrine\Common\Collections\ArrayCollection();
		$this->categories  = new \Doctrine\Common\Collections\ArrayCollection();
		$this->attachments = new \Doctrine\Common\Collections\ArrayCollection();
	}

	public function getLink()
	{
		$url = App::getRouter()->generate('user_articles_article', array('slug' => $this->getUrlSlug()), true);

		return $url;
	}

	public function getPermalink()
	{
		$url = App::getRouter()->generate('user_articles_article', array('slug' => $this->id), true);

		return $url;
	}

	public function addToCategory(ArticleCategory $cat)
	{
		$this->categories->add($cat);
	}

	public function setCategories(array $cats)
	{
		// Normalize array
		// Make sure we have real cat objects, index array by id
		$set = array();
		foreach ($cats as $cat) {
			if (!is_object($cat)) {
				$cat = App::findEntity('DeskPRO:ArticleCategory', $cat);
			}

			$set[$cat['id']] = $cat;
		}

		// Go through find which ones we need to add or remove
		$all_ids = $this->categories->getKeys();
		$new_ids = array_keys($set);

		$add = array_diff($new_ids, $all_ids);
		$del = array_diff($all_ids, $new_ids);

		foreach ($add as $cid) {
			$this->categories->add($set[$cid]);
		}
		foreach ($del as $cid) {
			$this->categories->remove($cid);
		}
	}

	public function setProducts(array $prods)
	{
		$set = array();
		foreach ($prods as $prod) {
			if (!is_object($prod)) {
				$prod = App::findEntity('DeskPRO:Product', $prod);
			}

			$set[$prod['id']] = $prod;
		}

		// Go through find which ones we need to add or remove
		$all_ids = $this->products->getKeys();
		$new_ids = array_keys($set);

		$add = array_diff($new_ids, $all_ids);
		$del = array_diff($all_ids, $new_ids);

		foreach ($add as $pid) {
			$this->products->add($set[$pid]);
		}
		foreach ($del as $pid) {
			$this->products->remove($pid);
		}
	}

	public function getCategoryNames($sep = ', ', $full = true)
	{
		$cats = array();
		foreach ($this->categories as $cat) {
			if ($full) {
				if ($full !== true) {
					// If its not a boolean, then its a string separator
					$cats[] = $cat->getFullTitle($full);
				} else {
					$cats[] = $cat->getFullTitle();
				}

			} else {
				$cats[] = $cat['title'];
			}
		}

		return implode($sep, $cats);
	}

	public function getRatingPercent()
	{
		if (!$this->total_rating) {
			return 0;
		}

		$neg_ratings = $this->num_ratings - $this->total_rating;
		$rating = ceil(($neg_ratings / $this->total_rating) * 100);

		return $rating;
	}

	public function getCategoryPath($index = 0)
	{
		$path = array();

		$cat = $this->categories[$index];
		$path[] = $cat;
		while ($cat['parent']) {
			$cat = $cat['parent'];
			$path[] = $cat;
		}

		return $path;
	}

	public function addAttachment(ArticleAttachment $attach)
	{
		$this->attachments->add($attach);
		$attach['article'] = $this;
	}
}
