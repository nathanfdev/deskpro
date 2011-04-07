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

namespace Application\DeskPRO\EntityRepository;

use \Application\DeskPRO\App;

use \Doctrine\ORM\EntityRepository;

class CommentArticle extends EntityRepository
{
	protected $_comment_fetcher = null;

	public function __call($method, $args)
	{
		$fetcher = $this->getCommentFetcher();

		if (method_exists($fetcher, $method)) {
			return call_user_func_array(array($fetcher, $method), $args);
		}
	}

	public function getCommentFetcher()
	{
		if ($this->_comment_fetcher !== null) return $this->_comment_fetcher;
		
		$this->_comment_fetcher = new \Application\DeskPRO\Comments\CommentFetcher();
		return $this->_comment_fetcher;
	}
}