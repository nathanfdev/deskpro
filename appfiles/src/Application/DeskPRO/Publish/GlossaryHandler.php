<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Addons
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Publish;

use Application\DeskPRO\App;

use Doctrine\ORM\EntityManager;
use Application\DeskPRO\DBAL\Connection;

use Orb\Util\Arrays;
use Orb\Util\Util;

/**
 * Handles linking glossary words in texts
 */
class GlossaryHandler
{
	/**
	 * Entity manager
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	/**
	 * Plain database connection for raw queries
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	protected $db;

	/**
	 * All words defined
	 * @var array
	 */
	protected $_words = null;

	protected $_defs = array();

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->db = $em->getConnection();
	}

	protected function _initWords()
	{
		if ($this->_words !== null) return;

		$this->_words = $this->db->fetchAllCol("
			SELECT word
			FROM glossary_words
		");
	}

	public function clear()
	{
		$this->_defs = array();
	}

	public function loadWords(array $words)
	{
		$load = array_diff($words, array_keys($this->_defs));
		if ($load) {
			$in_q = array_fill(0, count($load), '?');
			$in_q = implode(',');

			$words = $this->db->fetchAllKeyValue("
				SELECT word, content
				FROM glossary_words
				WHERE word IN ($in_q)
			", $load);

			$this->_defs = array_merge($this->_defs, $words);
		}
	}

	public function processText($text)
	{
		$this->_initWords();

		$load = array();
		foreach ($this->_words as $word) {
			if (preg_match('#\b' . preg_quote($word, '#') . '\b#i', $text)) {
				$load[] = $word;
			}
		}

		$url_base = App::getRouter()->generate('agent_glossary_word_tip', array('word' => '__DP_WORD__'));

		foreach ($load as $word) {
			$word_h = htmlentities($word);
			$word_u = urlencode($word);

			$text = preg_replace_callback(
				'#(\b)(' . preg_quote($word, '#') . ')(\b)#i',
				function($m) use ($word_h, $word_u, $url_base) {
					$url = str_replace('__DP_WORD__', $word_u, $url_base);

					return $m[1]
						. '<span class="embedded-glossary-word tipped" data-glossary-word="'.$word_h.'" data-tipped="'.$url.'" data-tipped-options="ajax:true">'
						. $m[2]
						. '</span>'
						. $m[3];
				},
				$text,
				1
			);
		}

		return $text;
	}
}
