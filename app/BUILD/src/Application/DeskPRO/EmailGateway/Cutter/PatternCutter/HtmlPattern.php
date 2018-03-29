<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Cutter\PatternCutter;

class HtmlPattern
{
    /**
     * @var string
     */
    protected $pattern;

    /**
     * @var array
     */
    protected $tokens;

    /**
     * Example pattern: div p ?a b span #from:#i /span /b span #.*# br /br b #sent:#i /b #.*# br /br b #to:#i /b #.*# br /br /span /p /div.
     *
     * @param $pattern
     */
    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Get tokens for the pattern.
     *
     * @return array
     */
    public function getTokens()
    {
        if ($this->tokens !== null) {
            return $this->tokens;
        }

        $pattern = " {$this->pattern} ";
        $pattern = str_replace('\\#', '__dp_esc_hash__', $pattern);

        $segments = preg_split('/ (#(?:.*?)#(?:[imsxADUu]*)) /', $pattern, null, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY);

        $depth = 0;
        foreach ($segments as $segment) {
            $segment = str_replace('__dp_esc_hash__', '\\#', $segment);
            $segment = trim($segment);

            // Match token is a regex string
            if ($segment[0] == '#') {
                $this->tokens[] = ['match', trim($segment)];

            // Tag token
            } else {
                // Space on each side for easy anchoring
                $segment = " $segment ";

                // Split up tags into groups of tags, optional tags and closing tags
                $tag_segments = preg_split('/ (\??\\/?(?:[a-zA-Z:]+)) /', $segment, null, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY);

                foreach ($tag_segments as $tag) {
                    $tag = trim($tag);
                    if (!$tag) {
                        continue;
                    }

                    // Closing tag: This just means :parent for us,
                    // its just telling the matcher to go up the tree again
                    if ($tag[0] == '/') {
                        $this->tokens[] = ['nav', ':close', $depth--];

                    // Normal tag, add it to the current tag bunch
                    } else {
                        $this->tokens[] = ['nav', $tag, $depth++];
                    }
                }
            }
        }

        return $this->tokens;
    }
}
