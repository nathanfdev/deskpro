<?php

/**
 * DeskPRO.
 */

namespace Orb\Html;

use DOMDocument;
use DOMDocumentType;
use DOMElement;
use DOMNode;
use DOMText;
use Orb\Util\Strings;

/**
 * Class Html2Text.
 *
 * Converts HTML documents into plaintext.
 *
 * Based on html2text by Jeven Wright: https://code.google.com/p/iaml/source/browse/trunk/org.openiaml.model.runtime/src/include/html2text/html2text.php
 */
class Html2Text
{
    private $bq_level = 0;

    /**
     * Array of tag => function.
     *
     * @var array
     */
    private $element_procs = [];

    /**
     * @param string $html
     *
     * @return string
     */
    public static function convertHtml($html)
    {
        $h2t = new self();

        return $h2t->convert($html);
    }

    /**
     * @param string   $tagname The tagname
     * @param callable $fn      Function to call. Return null and the default convertNode routine is run
     */
    public function addElementProcessor($tagname, $fn)
    {
        $this->element_procs[$tagname] = $fn;
    }

    /**
     * Convert an HTML string into plaintext.
     *
     * @param string $html
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function convert($html)
    {
        $html = Strings::standardEol($html);

        // nbsp's
        $html = trim($html);
        $html = str_replace('&nbsp;', 'xxxDP_NBSP_PLACExxx', $html);
        $html = preg_replace('#\x{00a0}#u', 'xxxDP_NBSP_PLACExxx', $html);

        $html = '<?xml version="1.0" encoding="UTF-8"?>'."\n".$html;

        $doc = new DOMDocument('1.0', 'UTF-8');
        if (!@$doc->loadHTML($html)) {
            throw new \InvalidArgumentException('Error loading HTML into DOMDocument');
        }

        $this->bq_level = 0;

        $txt = $this->convertNode($doc);
        $txt = str_replace('xxxDP_NBSP_PLACExxx', ' ', $txt);
        $txt = trim($txt);

        return $txt;
    }

    /**
     * Convert a DOMNode/DOMDocument into plaintext.
     *
     * @param DOMNode $node
     *
     * @return string
     */
    public function convertNode(DOMNode $node, $_depth = 0)
    {
        if ($node instanceof DOMText) {
            return trim($node->wholeText);
        }
        if ($node instanceof DOMDocumentType) {
            return '';
        }

        $nextName = $this->getNextChildName($node);

        $name = strtolower($node->nodeName);

        if (isset($this->element_procs[$name])) {
            $output = call_user_func($this->element_procs[$name], $node, $nextName, $this);
            if ($output !== null && $output !== false) {
                return $output;
            }
        }

        $output = '';
        switch ($name) {
            case 'hr':
                return '<DP_BR>------<DP_BR>';

            case 'style':
            case 'head':
            case 'title':
            case 'meta':
            case 'script':
            case 'object':
                return '';

            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':
                $output = '<DP_BR>';
                break;

            case 'tr':
                $output = '<DP_BR_P>';
                break;

            case 'p':
                $output = '<DP_BR>';
                break;

            case 'div':
                $output = '<DP_BR_P>';
                break;
        }

        if (!empty($node->childNodes)) {
            $len = $node->childNodes->length;
            for ($i = 0; $i < $len; ++$i) {
                $n = $node->childNodes->item($i);
                if ($n) {
                    $is_bq = false;
                    if ($n instanceof DOMElement && ($n->getAttribute('data-dp-type') === 'blockquote' || strtolower($n->nodeName) == 'blockquote')) {
                        $is_bq = true;
                        ++$this->bq_level;
                    }
                    $text = $this->convertNode($n, $_depth + 1);
                    if ($is_bq) {
                        --$this->bq_level;
                    }
                    $output .= $text;
                }
            }
        }

        // end whitespace
        switch ($name) {
            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':
                $output .= '<DP_BR>';
                break;

            case 'p':
            case 'br':
                    $output .= '<DP_BR>';
                break;

            case 'div':
                $output .= '<DP_BR_P>';
                break;

            case 'a':
                if (!trim(str_replace(['<DP_BR>', '<DP_BR_P>', 'xxxDP_NBSP_PLACExxx'], '', $output))) {
                    $output = '';
                } else {
                    $href = $node->getAttribute('href');
                    if ($href == null) {
                        // it doesn't link anywhere
                        if ($node->getAttribute('name') != null) {
                            $output = "[$output]";
                        }
                    } else {
                        if ($href == $output) {
                            // link to the same address: just use link
                        } else {
                            // replace it
                            $output = "[$output]($href)";
                        }
                    }

                    // does the next node require additional whitespace?
                    switch ($nextName) {
                        case 'h1': case 'h2': case 'h3': case 'h4': case 'h5': case 'h6':
                            $output .= '<DP_BR>';
                            break;
                    }
                }
                break;
        }

        $output = str_replace("\n", ' ', $output);

        $output = trim($output);

        if ($node instanceof DOMElement && ($node->getAttribute('data-dp-type') === 'blockquote' || $name == 'blockquote')) {
            $output = '<DP_BLOCKQUOTE_BEGIN_'.$this->bq_level.'>'.$output.'<DP_BLOCKQUOTE_END_'.$this->bq_level.'>';
            if ($name == 'blockquote') {
                $output .= '<DP_BR>'; // a real blockquote el has whitespace after it
            }
        }

        if ($_depth == 0) {
            $output = preg_replace('#<DP_BR_P>\s*<DP_BR>#m', '<DP_BR>', $output);
            $output = preg_replace('#<DP_BR>\s*<DP_BR_P>#m', '<DP_BR>', $output);
            $output = preg_replace_callback('#(<DP_BR_P>\s*)+#m', function ($m) {
                return str_repeat("\n", min(2, substr_count($m[1], '<DP_BR_P>')));
            }, $output);
            $output = str_replace('<DP_BR>', "\n", $output);
            $output = str_replace('<DP_SP>', ' ', $output);

            $output = preg_replace_callback('#<DP_BLOCKQUOTE_BEGIN_(\d+)>(.*?)<DP_BLOCKQUOTE_END_\\1>#ms', function ($m) {
                $s = str_repeat('>', $m[1]).' ';

                return Strings::modifyLines(trim($m[2]), $s);
            }, $output);
        }
        $output = preg_replace('#[ ]+#', ' ', $output); // multiple spaces to single

        $output = trim($output);

        return $output;
    }

    /**
     * @param DOMNode $node
     *
     * @return null|string
     */
    protected function getNextChildName(DOMNode $node)
    {
        // get the next child
        $nextNode = $node->nextSibling;
        while ($nextNode != null) {
            if ($nextNode instanceof DOMElement) {
                break;
            }
            $nextNode = $nextNode->nextSibling;
        }
        $nextName = null;
        if ($nextNode instanceof DOMElement && $nextNode != null) {
            $nextName = strtolower($nextNode->nodeName);
        }

        return $nextName;
    }

    /**
     * @param DOMNode $node
     *
     * @return null|string
     */
    protected function getPrevChildName(DOMNode $node)
    {
        $nextNode = $node->previousSibling;
        while ($nextNode != null) {
            if ($nextNode instanceof DOMElement) {
                break;
            }
            $nextNode = $nextNode->previousSibling;
        }
        $nextName = null;
        if ($nextNode instanceof DOMElement && $nextNode != null) {
            $nextName = strtolower($nextNode->nodeName);
        }

        return $nextName;
    }
}
