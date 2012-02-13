<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Publish
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Publish;

use Doctrine\ORM\EntityManager;

class LatestContent
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @var int
	 */
	protected $count = 10;

	/**
	 * @var int
	 */
	protected $max_article = 10;

	/**
	 * @var int
	 */
	protected $max_feedback = 10;

	/**
	 * @var int
	 */
	protected $max_download = 10;

	/**
	 * @var int
	 */
	protected $max_news = 10;


	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}


	/**
	 * @param $x
	 * @return LatestContent
	 */
	public function setMaxCount($x)
	{
		$this->count = $x;

		// They all have equal weight
		$this->max_article = $this->max_feedback = $this->max_download = $this->max_news = $x;

		return $this;
	}


	/**
	 * @param $x
	 * @return LatestContent
	 */
	public function setMaxArticles($x)
	{
		$this->max_article = $x;
		return $this;
	}


	/**
	 * @param $x
	 * @return LatestContent
	 */
	public function setMaxFeedback($x)
	{
		$this->max_feedback = $x;
		return $this;
	}


	/**
	 * @param $x
	 * @return LatestContent
	 */
	public function setMaxDownloads($x)
	{
		$this->max_download = $x;
		return $this;
	}


	/**
	 * @param $x
	 * @return LatestContent
	 */
	public function setMaxNews($x)
	{
		$this->max_news = $x;
		return $this;
	}


	/**
	 * @return array
	 */
	public function getResults()
	{
		$results = array();

		if ($this->max_article) {
			$res = $this->em->getRepository('DeskPRO:Article')->getNewest($this->max_article);
			foreach ($res as $r) {
				$results[] = array('type' => 'article', 'item' => $r);
			}
		}
		if ($this->max_feedback) {
			$res = $this->em->getRepository('DeskPRO:Feedback')->getNewest(null, $this->max_feedback);
			foreach ($res as $r) {
				$results[] = array('type' => 'feedback', 'item' => $r);
			}
		}
		if ($this->max_download) {
			$res = $this->em->getRepository('DeskPRO:Download')->getNewest($this->max_download);
			foreach ($res as $r) {
				$results[] = array('type' => 'download', 'item' => $r);
			}
		}
		if ($this->max_news) {
			$res = $this->em->getRepository('DeskPRO:News')->getNewest($this->max_news);
			foreach ($res as $r) {
				$results[] = array('type' => 'news', 'item' => $r);
			}
		}

		usort($results, function($a, $b) {
			return ($a['item']->date_created < $b['item']->date_created) ? -1 : 1;
		});

		if (count($results) <= $this->count) {
			$final_results = $results;
		} else {
			$final_results = array();
			$counts = array();

			foreach ($results as $r) {
				if (!isset($counts[$r['type']])) {
					$counts[$r['type']] = 0;
				}

				$prop = 'max_' . $r['type'];

				if ($counts[$r['type']] >= $this->$prop) {
					continue;
				}

				$final_results[] = $r;
				$counts[$r['type']]++;

				if (count($final_results) >= $this->count) {
					break;
				}
			}
		}

		return $final_results;
	}
}
