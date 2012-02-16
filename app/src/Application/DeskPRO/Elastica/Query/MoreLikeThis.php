<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Elastica
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Elastica\Query;

/**
 * MLT query type
 *
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/mlt-query.html
 */
class MoreLikeThis extends \Elastica_Query_Abstract
{
	protected $_like_text = '';

	public function setLikeText($like_text)
	{
		$this->_like_text = $like_text;
	}

	public function toArray()
	{
		return array('more_like_this' => array(
			'like_this' => $this->_like_text
		));
	}
}