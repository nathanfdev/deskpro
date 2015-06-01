<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\SassCompiler\Filter;

use DeskPRO\Component\Util\RandUtils;

/**
 * Removes absolute and relative paths in @import's.
 * Means only files in the include paths can be imported.
 *
 * This "fixes" it by replacing bad @imports with a token,
 * and then putting them back after SCSS has run (thus it's possible
 * for someone to use an http url for example and still have it @import via usual css).
 */
class SafeImportIncPathFilter implements FilterInterface
{
    /**
     * @var array
     */
    private $tokens = array();

    /**
     * Called on a source file BEFORE scss has been compiled.
     *
     * @param string $file_name
     * @param string $source
     * @return string
     */
    public function preProcessSource($file_name, $source)
    {
        $tokens = array();
        $source = preg_replace_callback('#@import\s+(.*?);#i', function($m) use (&$tokens, $source) {
            $url = trim(trim(trim($m[1]), "'\""));
            if (!preg_match('#^[a-zA-Z0-9_\-_][a-zA-Z0-9_\-_\.\\/]#', $url)) {
                $t = RandUtils::randomBodyToken($source);
                $tokens[$t] = $m[0];
                return $t;
            } else {
                return $m[0];
            }
        }, $source);

        $this->tokens = $tokens;

        return $source;
    }

    /**
     * Called on the result AFTER scss has been compiled.
     *
     * @param string $source
     * @return string
     */
    public function postProcessResult($source)
    {
        if (!$this->tokens) {
            return $source;
        }

        return str_replace(array_keys($this->tokens), array_values($this->tokens), $source);
    }
}