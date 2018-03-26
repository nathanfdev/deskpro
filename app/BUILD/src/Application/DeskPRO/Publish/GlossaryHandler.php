<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Publish;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Brand;
use Doctrine\ORM\EntityManager;

/**
 * Handles linking glossary words in texts.
 */
class GlossaryHandler
{
    /**
     * Entity manager.
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * Plain database connection for raw queries.
     *
     * @var \Application\DeskPRO\DBAL\Connection
     */
    protected $db;

    /**
     * @var Brand
     */
    protected $brand;

    /**
     * All words defined.
     *
     * @var array
     */
    protected $_words = null;

    /**
     * @var array
     */
    protected $_defs = [];

    public function __construct(EntityManager $em, Brand $brand)
    {
        $this->em    = $em;
        $this->db    = $em->getConnection();
        $this->brand = $brand;
    }

    protected function _initWords()
    {
        if ($this->_words !== null) {
            return;
        }

        $qb = $this->db->createQueryBuilder();
        $qb->select(['word'])
            ->from('glossary_words', 'gw')
            ->andWhere($qb->expr()->eq('gw.brand_id', '?'));

        $this->_words = $this->db->fetchAllCol($qb->getSQL(), [$this->brand->getId()]);
    }

    public function clear()
    {
        $this->_defs = [];
    }

    public function loadWords(array $words)
    {
        if (!$words) {
            return;
        }

        $load = array_diff($words, array_keys($this->_defs));
        if ($load) {
            $qb = $this->db->createQueryBuilder();
            $qb->select(['word', 'gwd.definition'])
                ->from('glossary_words', 'gw')
                ->innerJoin('gw', 'glossary_word_definitions', 'gwd', 'gw.definition_id = gwd.id')
                ->andWhere($qb->expr()->in('word', '?'))
                ->andWhere($qb->expr()->eq('gw.brand_id', '?'))
                ->setParameter('load', $load);
            $words = $this->db->fetchAllKeyValue($qb->getSQL(), [$load, $this->brand->getId()],
                [Connection::PARAM_STR_ARRAY]);

            $this->_defs = array_merge($this->_defs, $words);
        }
    }

    public function getWordDefs(array $words = [])
    {
        $this->loadWords($words);

        return $this->_defs;
    }

    /**
     * @param $text
     *
     * @return array
     */
    public function findWords($text)
    {
        $this->_initWords();

        $load = [];
        foreach ($this->_words as $word) {
            if (preg_match('#\b'.preg_quote($word, '#').'\b#i', $text)) {
                $load[] = $word;
            }
        }

        return $load;
    }

    /**
     * @param $text
     *
     * @return mixed
     */
    public function processText($text)
    {
        $this->_initWords();

        $load = [];
        foreach ($this->_words as $word) {
            if (preg_match('#\b'.preg_quote($word, '#').'\b#i', $text)) {
                $load[] = $word;
            }
        }

        $url_base = App::getRouter()->generate('agent_glossary_word_tip', ['word' => '__DP_WORD__']);

        foreach ($load as $word) {
            $word_h = htmlentities($word);
            $word_u = urlencode($word);

            $text = preg_replace_callback(
                '#(\b)('.preg_quote($word, '#').')(\b)#i',
                function ($m) use ($word_h, $word_u, $url_base) {
                    $url = str_replace('__DP_WORD__', $word_u, $url_base);

                    return $m[1]
                    .'<span class="embedded-glossary-word tipped" data-glossary-word="'.$word_h.'" data-tipped="'.$url.'" data-tipped-options="ajax:true">'
                    .$m[2]
                    .'</span>'
                    .$m[3];
                },
                $text,
                1
            );
        }

        return $text;
    }
}
