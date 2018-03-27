<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO;

use Orb\Util\Strings;
use Orb\Util\Util as OrbUtil;

/**
 * Transform markdown-formatted text into html.
 */
class Markdown extends \Markdown_Parser
{
    /** @var array */
    protected $attach_tokens = [];

    /**
     * Format the supplied markdown string to HTML.
     *
     * @static
     *
     * @param  $string
     *
     * @return string
     */
    public static function format($string)
    {
        $tr = new self();

        return $tr->transform($string);
    }

    public function transform($text)
    {
        $this->attach_tokens = [];

        // Get rid of more tokens, they'd've been handled elsewhere
        $text = str_replace('![more]', '', $text);

        $m = null;
        if (preg_match_all('#!\[attach(.*?)\]#', $text, $m)) {
            foreach ($m[0] as $match) {
                $token                       = ':attach-token-'.md5(microtime().OrbUtil::requestUniqueId()).':';
                $this->attach_tokens[$token] = $match;
            }
        }

        $text = parent::transform($text);

        if ($this->attach_tokens) {
            $text = $this->processAttachTokens($text, $this->attach_tokens);
        }

        $this->attach_tokens = [];

        return $text;
    }

    public function processAttachTokens($text, array $attach_tokens)
    {
        foreach ($attach_tokens as $token => $attach_code) {
            $attach_html = $this->getAttachHtml($attach_code);
            $text        = str_replace($token, $attach_html, $text);
        }

        return $text;
    }

    public function getAttachHtml($attach_code)
    {
        $blob_id = Strings::extractRegexMatch('#!\[attach:(\d+)#', $attach_code, 1);
        if (!$blob_id) {
            return '';
        }

        /** @var $blob \Application\DeskPRO\Entity\Blob */
        $blob = App::findEntity('DeskPRO:Blob', $blob_id);
        if (!$blob) {
            return '';
        }

        if (strpos('url]', $attach_code) !== null) {
            return $blob->getDownloadUrl();
        } elseif (strpos('image]', $attach_code) !== null) {
            return '<img src="'.$blob->getDownloadUrl().'" alt="" class="blob blob-'.$blob['id'].'" border="0" />';
        }

        return '';
    }
}
